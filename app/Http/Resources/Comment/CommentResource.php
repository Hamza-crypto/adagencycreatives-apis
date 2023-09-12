<?php

namespace App\Http\Resources\Comment;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $this->user;

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_id' => $user->uuid,
            'user' => $user->first_name.' '.$user->last_name,
            'post_id' => $this->post->uuid,
            'parent_id' => isset($this->parent) ? $this->parent->uuid : null,
            'content' => $this->content,
            'created_at' => $this->created_at->format(config('global.datetime_format')),
            'updated_at' => $this->created_at->format(config('global.datetime_format')),
        ];
    }
}