<?php

namespace App\Http\Resources\Application;

use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'type' => 'applications',
            'id' => $this->uuid,
            'user_id' => $this->user->uuid,
            'job_id' => $this->job->uuid,
            'resume_url' => $this->attachment_id,
            'message' => $this->message,
            'status' => $this->status,
            'created_at' => $this->created_at->format(config('global.datetime_format')),
            'updated_at' => $this->created_at->format(config('global.datetime_format')),

            'relationships' => [
                'notes' => [
                    'links' => [
                        'related' => route('notes.index') . '?filter[application_id]=' . $this->uuid,
                    ],
                ],
            ],

        ];

    }
}
