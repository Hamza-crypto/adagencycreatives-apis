<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Subscription\SubscriptionResource;
use App\Jobs\SendEmailJob;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $subscriptions = $user->subscriptions()->get();

        return $subscriptions;
    }

    public function show(Plan $plan, Request $request)
    {
        $intent = auth()->user()->createSetupIntent();

        return [
            'intent' => $intent,
            'plan' => $plan,
        ];
    }

    public function subscription(Request $request)
    {
        try {
            $plan = Plan::find($request->plan_id);
            $user = $request->user();

            $subscription = $user->newSubscription($plan->slug, $plan->stripe_plan)
                ->create($request->token);

            $totalQuota = $plan->quota;
            $endDate = Carbon::now()->addDays($plan->days);

            $subscription->update([
                'quota_left' => $totalQuota,
                'ends_at' => $endDate,
            ]);

            $order = $user->orders()->create([
                'plan_id' => $plan->id,
                'amount' => $plan->price,
            ]);

            $data = [
                'order_no' => $order->id,
                'username' => $user->first_name.' '.$user->last_name,
                'email' => $user->email,
                'total' => $plan->price,
                'pm_type' => $subscription->owner->pm_type,
                'created_at' => \Carbon\Carbon::parse($subscription->created_at)->format('F d, Y'),
            ];

            $admin = User::find(1);
            SendEmailJob::dispatch([
                'receiver' => $admin,
                'data' => $data,
            ], 'order_confirmation');

            return $subscription;
        } catch (\Exception $e) {
            throw new ApiException($e, 'STRIPE-01');
        }
    }

    public function cancel(Request $request)
    {
        $user = $request->user();
        foreach ($user->subscriptions as $subscription) {
            $subscription->cancelNow();
        }

        return [
            'message' => 'Subscriptions cancelled',
        ];
    }

    public function status(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscriptions()->select('name', 'quota_left', 'ends_at')->latest()->first();

        return new SubscriptionResource($subscription);

    }
}
