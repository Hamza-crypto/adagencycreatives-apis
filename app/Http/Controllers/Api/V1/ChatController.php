<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\MessageReceived;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Http\Resources\Message\MessageCollection;
use App\Http\Resources\Message\MessageResource;
use App\Http\Resources\User\UserResource;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index(Request $request, $contactId)
    {
        $contact = User::where('uuid', $contactId)->firstOrFail();
        $contactId = $contact->id;

        $userId = request()->user()->id;

        $messages = Message::where(function ($query) use ($userId, $contactId) {
            $query->where(function ($query) use ($userId, $contactId) {
                $query->where('sender_id', $userId)
                    ->where('receiver_id', $contactId);
            })->orWhere(function ($query) use ($userId, $contactId) {
                $query->where('sender_id', $contactId)
                    ->where('receiver_id', $userId);
            });
        });

        $types = [];
        // Add the dynamic type condition if provided in the request
        if ($request->has('type')) {
            $types = explode(',', $request->type);
            $messages->whereIn('type', $types);
        }

        $messages = $messages->latest()
            ->paginate($request->per_page ?? config('global.request.pagination_limit'));

        // Read all messages between these two users
        Message::where('sender_id', $contactId)
        ->where('receiver_id', $userId)
        ->whereIn('type', $types)
        ->whereNull('read_at')
        ->touch('read_at');

        //    dd($messages);
        return new MessageCollection($messages);
    }

    public function store(StoreMessageRequest $request)
    {
        try {
            $sender = User::where('uuid', $request->sender_id)->first();
            $receiver = User::where('uuid', $request->receiver_id)->first();
            $type = $request->type ?? 'private';

            $event_data = [
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'type' => 'received',
            ];
            $request->merge([
                'uuid' => Str::uuid(),
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'message' => $request->message,
            ]);

            //Mark previous messages as read
            Message::where('type', $type)
                ->where('receiver_id', $sender->id) // Only those messages in which I am receiver,
                ->where('sender_id', $receiver->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $message = Message::create($request->all());
            $msg_resource = new MessageResource($message, $sender->uuid);
            event(new MessageReceived($event_data));

            return $msg_resource;
        } catch (\Exception $e) {
            throw new ApiException($e, 'MS-01');
        }
    }

    public function getAllMessageContacts(Request $request) // Get list of contacts to be shown on left panel
    {
        $userId = request()->user()->id;

        $contacts = Message::with('sender', 'receiver');

         $types = [];
        // Add the dynamic type condition if provided in the request
        if ($request->has('type')) {
            $types = explode(',', $request->type);
            $contacts->whereIn('type', $types);
        }

        $contacts = $contacts->where(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                ->orWhere('receiver_id', $userId);
        });

        $contacts = $contacts->latest()->get();

        $uniqueContacts = [];
        $uniquePairs = []; // To store unique pairs

        try {
            foreach ($contacts as $contact) {
                $senderId = $contact->sender_id;
                $receiverId = $contact->receiver_id;

                $sortedPair = [$senderId, $receiverId];
                sort($sortedPair);

                // Check if the reverse pair is already added
                if (!in_array($sortedPair, $uniquePairs)) {

                    if (!isset($contact->receiver)) {
                        continue;
                    }
                    if (!isset($contact->sender)) {
                        continue;
                    }

                    if ($senderId == $userId) {
                        $contact->message_type = 'sent';
                        unset($contact['sender']);
                        $contact->contact = new UserResource($contact->receiver);
                        unset($contact['receiver']);

                    } elseif ($receiverId == $userId) {
                        $contact->message_type = 'received';
                        unset($contact['receiver']);
                        $contact->contact = new UserResource($contact->sender);
                        unset($contact['sender']);

                    }

                    $uniquePairs[] = $sortedPair;
                    $uniqueContacts[] = $contact;
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['contacts' => $uniqueContacts]);
    }

    private function getMessageType($sender1, $current_user)
    {
        return $sender1->uuid === $current_user->uuid ? 'sent' : 'received';
    }

    public function mark_as_read(Request $request, $sender_id)
    {
        $user = $request->user();
        $sender = User::where('uuid', $sender_id)->first();
        $msg_type = $request->type;

        Message::where('sender_id', $sender->id)
            ->where('receiver_id', $user->id)
            ->where('type', $msg_type)
            ->whereNull('read_at')
            ->touch('read_at');

        return response()->json(['success' => true], 200);
    }
}