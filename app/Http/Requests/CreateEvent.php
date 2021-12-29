<?php

namespace App\Http\Requests;

use App\Rules\Checkbox;
use App\Rules\Visibility;
use App\Rules\YoutubeLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateEvent extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->isPremium();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:25',
            'description' => 'nullable|string|max:255',
            'date_time' => 'required|date_format:Y-m-d\TH:i',
            'thumbnail' => 'required|mimes:jpg,jpeg,png|max:5000',
            'youtube' => ['required_without_all:thumbnail,video', 'nullable', 'active_url', new YoutubeLink],
            'video' => 'required_without_all:thumbnail,youtube|mimes:mp4,mov,mpg,mpeg|max:500000',
            'spots' => 'required|array',
            'spots.*' => 'numeric|exists:App\Models\Spot,id',
            'visibility' => ['required', new Visibility],
            'link_access' => new Checkbox,
            'accept_method' => 'required|in:none,invite,accept',
            'users' => 'array',
            'users.*' => 'numeric|exists:App\Models\User,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'video.max' => 'The video must be less than 500MB',
            'thumbnail.max' => 'The thumbnail must be less than 5MB',
        ];
    }
}
