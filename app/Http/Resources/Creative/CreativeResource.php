<?php

namespace App\Http\Resources\Creative;

use Illuminate\Http\Resources\Json\JsonResource;

class CreativeResource extends JsonResource
{
    private $category;

    private $location;

    public function toArray($request)
    {
        $user = $this->user;

        $this->category = isset($this->category) ? $this->category->name : null;

        $this->location = $this->get_location($user);

        return [
            'type' => 'creatives',
            'id' => $this->uuid,
            'user_id' => $this->user->uuid,
            'name' => $this->user->first_name.' '.$this->user->last_name,
            'slug' => $this->slug,
            'title' => $this->title,
            'category' => $this->category,
            'profile_image' => $this->get_profile_image($user),
            'years_of_experience' => $this->years_of_experience,
            'about' => $this->about,
            'employment_type' => $this->employment_type,
            'industry_experience' => getIndustryNames($this->industry_experience),
            'media_experience' => getMediaNames($this->media_experience),
            'character_strengths' => getCharacterStrengthNames($this->strengths),
            'priority' => [
                'is_featured' => $this->is_featured,
                'is_urgent' => $this->is_urgent,
            ],
            'workplace_preference' => [
                'is_remote' => $this->is_remote,
                'is_hybrid' => $this->is_hybrid,
                'is_onsite' => $this->is_onsite,
            ],
            'is_opentorelocation' => $this->is_opentorelocation,
            'location' => $this->location,
            'seo' => $this->generate_seo(),
            'created_at' => $this->created_at->format(config('global.datetime_format')),
            'updated_at' => $this->created_at->format(config('global.datetime_format')),

        ];
    }

    public function get_profile_image($user)
    {
        return isset($user->profile_picture) ? getAttachmentBasePath().$user->profile_picture->path : null;
    }

    public function get_location($user)
    {
        $address = collect($user->addresses)->firstWhere('label', 'personal');

        return $address ? [
            'state' => $address->state->name,
            'city' => $address->city->name,
        ] : null;
    }

    public function generate_seo()
    {
        $site_name = settings('site_name');
        $separator = settings('separator');

        $seo_title = $this->generateSeoTitle($site_name, $separator);
        $seo_description = $this->generateSeoDescription($site_name, $separator);

        return [
            'title' => $seo_title,
            'description' => $seo_description,
            'tags' => $this->seo_keywords,
        ];

    }

    private function generateSeoTitle($site_name, $separator)
    {
        $seo_title_format = $this->seo_title ? $this->seo_title : settings('creative_title');

        return replacePlaceholders($seo_title_format, [
            '%creatives_first_name%' => $this->user->first_name,
            '%creatives_last_name%' => $this->user->last_name,
            '%creatives_title%' => $this->title,
            '%creatives_location%' => isset($this->location) ? sprintf('%s, %s', ($this->location['city'] ? $this->location['city'] : ''), ($this->location['state'] ? $this->location['state'] : '')) : '',
            '%site_name%' => $site_name,
            '%separator%' => $separator,
        ]);
    }

    private function generateSeoDescription($site_name, $separator)
    {
        $seo_description_format = $this->seo_description ? $this->seo_description : settings('creative_description');

        return replacePlaceholders($seo_description_format, [
            '%creatives_about%' => $this->about,
            '%site_name%' => $site_name,
            '%separator%' => $separator,
        ]);
    }
}
