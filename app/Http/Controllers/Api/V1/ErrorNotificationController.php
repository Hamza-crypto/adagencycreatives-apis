<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiException;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\StoreNotificationRequest;
use App\Http\Resources\Notification\NotificationCollection;
use App\Http\Resources\Notification\NotificationResource;
use App\Jobs\SendEmailJob;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ErrorNotificationController extends Controller
{
    public function index(Request $request)
    {

        $ipAddress = $request->ip();
        $userAgent = $request->header('User-Agent');

        $admin = User::where('email', env('ADMIN_EMAIL'))->first();
        SendEmailJob::dispatch([
            'receiver' => $admin,
            'data' => [
                'url' => $request->url ?? '',
                'error' => $request->error ?? '',
                'date_time' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]
        ], 'error_notification');

        return json_encode(['status' => 'notified']);
    }
}