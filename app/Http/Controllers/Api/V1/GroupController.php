<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Group\StoreGroupRequest;
use App\Http\Resources\Group\GroupCollection;
use App\Http\Resources\Group\GroupResource;
use App\Models\Attachment;
use App\Models\Friendship;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $query = QueryBuilder::for(Group::class)
            ->allowedFilters([
                'name',
                'status',
                AllowedFilter::scope('user_id'),
            ]);

        $groups = $query->with('attachment')
            ->paginate($request->per_page ?? config('global.request.pagination_limit'));

        return new GroupCollection($groups);
    }

    public function create()
    {
        return view('pages.groups.add');
    }

    public function store(StoreGroupRequest $request)
    {
        $user = $request->user();

        $group = Group::create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description ?? '',
            'status' => $request->status ?? 'public',
        ]);

        GroupMember::create([
            'uuid' => Str::uuid(),
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        if ($request->hasFile('file')) {
            $attachment = storeImage($request, $user->id, 'cover_image');

            if (isset($attachment) && is_object($attachment)) {
                Attachment::whereId($attachment->id)->update([
                    'resource_id' => $group->id,
                ]);
            }
        }

        return new GroupResource($group);
    }

    public function update(Request $request, $uuid)
    {
        try {
            $group = Group::where('uuid', $uuid)->firstOrFail();
            $group->update($request->only('status'));

            return new GroupResource($group);
        } catch (\Exception $e) {
            throw new ApiException($e, 'US-01');
        }
    }

    public function destroy($uuid)
    {
        try {
            $group = Group::where('uuid', $uuid)->firstOrFail();
            $group->delete();

            return new GroupResource($group);
        } catch (\Exception $e) {
            throw new ApiException($e, 'US-01');
        }
    }

    public function get_groups(Request $request)
    {
        $cacheKey = 'all_groups';
        $groups = Cache::remember($cacheKey, now()->addMinutes(60), function () {
            return Group::select('uuid', 'name', 'status')->get();
        });

        return $groups;
    }

    public function sidebar_count()
    {
        try {

            $user = request()->user();
            $unread_messages = Message::where('receiver_id', $user->id)->whereNull('read_at')->count();
            $groups = Group::where('status', 0)->get();
            $friends_count = Friendship::where('user1_id', $user->id)->orWhere('user2_id', $user->id)->count();
            $members_count = User::where('role', 4)->where('is_visible', 1)->count();

            $stats = [
                'members_count' => $members_count,
                'friends_count' => $friends_count,
                'groups_count' => $groups->count(),
                'messages_count' => $unread_messages,
                'notifications_count' => $user->notifications()->whereNull('read_at')->count(),
            ];
            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}