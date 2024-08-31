<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $this->user;

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_id' => $user->uuid,
            'user' => $user->full_name,
            'user_slug' => get_user_slug($user),
            'profile_picture' => get_profile_picture($user),
            'profile_picture_base64' => $this->get_website_preview_base64($user),
            'target_id' => $this->target->uuid,
            'comment' => $this->comment,
            'rating' => $this->rating,
            'status' => $this->status,
            'created_at' => $this->created_at->format(config('global.datetime_format')),
            'updated_at' => $this->created_at->format(config('global.datetime_format')),
        ];
    }

    public function get_profile_picture_base64($user)
    {
        try {
            $profile_picture = get_profile_picture($user);
            return "data:image/jpeg;charset=utf-8;base64," . (strlen($profile_picture) > 0 ? base64_encode(file_get_contents($profile_picture)) : "");
        } catch (\Exception $e) {
        }
        return "data:image/jpeg;charset=utf-8;base64,";
    }
}