<?php

namespace App\Http\Requests;

use App\Rules\Checkbox;
use App\Rules\Hometown;
use App\Rules\Instagram;
use App\Rules\NotAutoUsername;
use App\Rules\UniqueOrOldEmail;
use App\Rules\YoutubeChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $imageMax = Auth::user()->isPremium() ? '5000' : '500';
        $notificationSettings = implode(',', array_keys(config('settings.notifications')));
        $privacySettings = implode(',', array_keys(config('settings.privacy')));
        return [
            'name' => ['sometimes', 'string', 'max:25', new NotAutoUsername],
            'email' => ['required_with:account-form', 'string', 'email', 'max:255', new UniqueOrOldEmail],
            'old_profile_image' => 'nullable|string|max:255',
            'old_cover_image' => 'nullable|string|max:255',
            'profile_image' => 'nullable|mimes:jpg,jpeg,png|max:' . $imageMax,
            'cover_image' => 'nullable|mimes:jpg,jpeg,png|max:' . $imageMax,
            'hometown' => ['string', 'max:255', new Hometown],
            'instagram' => ['nullable', 'active_url', new Instagram],
            'youtube' => ['nullable', 'active_url', new YoutubeChannel],
            'notifications' => 'required_with:notification-form|in:on-site,email,email-site,none|array:' . $notificationSettings,
            'privacy' => 'required_with:privacy-form|in:nobody,request,follower,anybody,private,public|array:' . $privacySettings,
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [];
        if (Auth::user()->isPremium()) {
            $messages = [
                'profile_image.max' => 'The image must be less than 5MB',
                'cover_image.max' => 'The image must be less than 5MB',
            ];
        }
        return $messages;
    }
}
