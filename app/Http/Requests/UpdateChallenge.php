<?php

namespace App\Http\Requests;

use App\Rules\Visibility;
use App\Rules\YoutubeLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateChallenge extends FormRequest
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
        $imageMax = '500';
        $video = [];
        if (Auth::user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium')) {
            $imageMax = '50000';
            $video = [
                'video' => 'mimes:mp4,mov,mpg,mpeg|max:500000'
            ];
        }

        return array_merge([
            'name' => 'required|string|max:25',
            'description' => 'required|string|max:255',
            'difficulty' => 'required|integer|between:1,5',
            'youtube' => ['nullable', 'active_url', new YoutubeLink],
            'thumbnail' => 'nullable|mimes:jpg,jpeg,png|max:' . $imageMax,
            'visibility' => ['required', new Visibility],
        ], $video);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [];
        if (Auth::user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium')) {
            $messages = [
                'video.max' => 'The video must be less than 500MB',
                'thumbnail.max' => 'The thumbnail must be less than 5MB',
            ];
        }
        return $messages;
    }
}
