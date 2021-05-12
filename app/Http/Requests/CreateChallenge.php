<?php

namespace App\Http\Requests;

use App\Rules\Visibility;
use App\Rules\YoutubeLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateChallenge extends FormRequest
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
        if (Auth::user()->isPremium()) {
            $content = [
                'youtube' => ['required_without:video', 'nullable', 'active_url', new YoutubeLink],
                'video' => 'required_without:youtube|mimes:mp4,mov,mpg,mpeg|max:500000',
                'thumbnail' => 'mimes:jpg,jpeg,png|max:5000',
            ];
        } else {
            $content = [
                'youtube' => ['required', 'active_url', new YoutubeLink],
                'thumbnail' => 'mimes:jpg,jpeg,png|max:500',
            ];
        }

        return array_merge([
            'spot' => 'required|integer|exists:App\Spot,id',
            'name' => 'required|string|max:25',
            'description' => 'required|string|max:255',
            'difficulty' => 'required|integer|between:1,5',
            'visibility' => ['required', new Visibility],
        ], $content);
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
                'youtube.required_without' => 'You must provide either a Youtube link or video file',
                'video.required_without' => 'You must provide either a video file or Youtube link',
                'video.max' => 'The video must be less than 500MB',
                'thumbnail.max' => 'The thumbnail must be less than 5MB',
            ];
        }
        return $messages;
    }
}
