<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Attachment;
use App\Models\Creative;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Link;
use App\Models\Location;
use App\Models\Phone;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CreativeController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function update(Request $request, $uuid)
    {
        // dd($request->all());
        $creative = Creative::where('uuid', $uuid)->first();
        $user = User::where('id', $creative->user_id)->first();
        $user->update([
            'is_visible' => $request->is_visible,
        ]); //Present in users table

        $uuid = Str::uuid();
        $this->userService->appendWorkplacePreference($request);

        $data = $request->only([
            'years_of_experience',
            'type_of_work',
            'is_featured',
            'is_urgent',
            'is_remote',
            'is_hybrid',
            'is_onsite',
            'is_opentorelocation',
            'about',
        ]);
        foreach ($data as $key => $value) {
            $creative->$key = $value;
        }
        $creative->save();

        if ($request->input('phone') != null) {
            $this->updatePhone($user, $request->input('phone'));
        }

        if ($request->has('linkedin') && $request->input('linkedin') != null) {
            $this->updateLink($user, 'linkedin', $request->input('linkedin'));
        }

        if ($request->has('file') && is_object($request->file)) {
            //Delete Previous picture
            if ($user->attachments->where('resource_type', 'profile_picture')->count()) {
                Attachment::where('user_id', $user->id)->where('resource_type', 'profile_picture')->delete();
            }

            $attachment = storeImage($request, $user->id, 'profile_picture');

            if (isset($attachment) && is_object($attachment)) {
                Attachment::whereId($attachment->id)->update([
                    'resource_id' => $creative->id,
                ]);
            }
        }

        $this->updateLocation($request, $user);

        // dd($request->all());
        Session::flash('success', 'Creative updated successfully');

        return redirect()->back();

    }

    public function update_qualification(Request $request, $uuid)
    {
        $creative = Creative::where('uuid', $uuid)->first();
        $user = User::where('id', $creative->user_id)->first();
        $uuid = Str::uuid();

        if ($request->input('portfolio') != null) {
            $this->updateLink($user, 'portfolio', $request->input('portfolio'));
        }

        if ($request->input('linkedin') != null) {
            $this->updateLink($user, 'linkedin', $request->input('linkedin'));
        }

        $creative->update([
            'title' => $request->title,
            'industry_experience' => ''.implode(',', $request->industry_experience).'',
            'media_experience' => ''.implode(',', $request->media_experience).'',
            'strengths' => ''.implode(',', $request->strengths ? $request->strengths : []).'',
        ]);

        Session::flash('success', 'Creative updated successfully');

        return redirect()->back();

    }

    public function update_experience(Request $request, $uuid)
    {
        $experience_ids = $request->input('experience_id');
        $titles = $request->input('title');
        $companies = $request->input('company');
        $descriptions = $request->input('description');

        foreach ($experience_ids as $key => $experience_id) {
            $experience = Experience::find($experience_id);
            $experience->title = $titles[$key];
            $experience->company = $companies[$key];
            $experience->description = $descriptions[$key];
            $experience->save();
        }

        Session::flash('success', 'Creative updated successfully');

        return redirect()->back();
    }

    public function update_education(Request $request, $uuid)
    {

        $education_ids = $request->input('education_id');
        $degree = $request->input('degree');
        $college = $request->input('college');

        foreach ($education_ids as $key => $education_id) {
            $education = Education::find($education_id);
            $education->degree = $degree[$key];
            $education->college = $college[$key];
            $education->save();
        }

        Session::flash('success', 'Creative updated successfully');

        return redirect()->back();
    }

    private function updatePhone($user, $phone_number)
    {
        $country_code = '+1';

        if (strpos($phone_number, $country_code) === 0) {
            $phone_number = substr($phone_number, strlen($country_code));
            $phone_number = trim($phone_number);
        }

        $phone = Phone::where('user_id', $user->id)->where('label', 'personal')->first();
        if ($phone) {
            $phone->update(['country_code' => $country_code, 'phone_number' => $phone_number]);
        } else {

            Phone::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'label' => 'personal',
                'country_code' => $country_code,
                'phone_number' => $phone_number,
            ]);
        }
    }

    private function updateLink($user, $label, $url)
    {
        $link = Link::where('user_id', $user->id)->where('label', $label)->first();
        if ($link) {
            $link->update(['url' => $url]);
        } else {

            Link::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'label' => $label,
                'url' => $url,
            ]);
        }
    }

    private function updateLocation($request, $user)
    {
        $state = Location::where('uuid', $request->state)->first();
        $city = Location::where('uuid', $request->city)->first();
        if ($state && $city) {
            $address = $user->addresses->first();
            if (! $address) {
                $address = new Address();
                $address->uuid = Str::uuid();
                $address->user_id = $user->id;
                $address->label = 'personal';
                $address->country_id = 1;
            }
            $address->state_id = $state->id;
            $address->city_id = $city->id;
            $address->save();
        }
    }
}
