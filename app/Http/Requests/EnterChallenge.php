<?php

namespace App\Http\Requests;

use App\Rules\YoutubeLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EnterChallenge extends FormRequest
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
            $rules = [
                'youtube' => ['required_without:video', 'nullable', 'active_url', new YoutubeLink],
                'video' => 'required_without:youtube|mimes:mp4,mov,mpg,mpeg|max:500000',
            ];
        } else {
            $rules = [
                'youtube' => ['required', 'active_url', new YoutubeLink],
            ];
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        if (Auth::user()->isPremium()) {
            return [
                'youtube.required_without' => 'You must provide either a Youtube link or video file',
                'video.required_without' => 'You must provide either a video file or Youtube link',
                'video.max' => 'The video must be less than 500MB',
            ];
        }

        return [];
    }
}
