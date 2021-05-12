<?php

namespace App\Http\Requests;

use App\Rules\Checkbox;
use App\Rules\Hometown;
use App\Rules\NotAutoUsername;
use App\Rules\UniqueOrOldEmail;
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
        return [
            'name' => ['sometimes', 'string', 'max:25', new NotAutoUsername],
            'email' => ['required_with:account_form', 'string', 'email', 'max:255', new UniqueOrOldEmail],
            'old_profile_image' => 'nullable|string|max:255',
            'old_cover_image' => 'nullable|string|max:255',
            'profile_image' => 'nullable|mimes:jpg,jpeg,png|max:' . $imageMax,
            'cover_image' => 'nullable|mimes:jpg,jpeg,png|max:' . $imageMax,
            'hometown' => ['string', 'max:255', new Hometown],
            'subscribed' => new Checkbox,
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
