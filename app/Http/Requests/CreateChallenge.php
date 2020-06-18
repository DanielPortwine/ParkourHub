<?php

namespace App\Http\Requests;

use App\Rules\YoutubeLink;
use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'spot' => 'required|integer|exists:App\Spot,id',
            'name' => 'required|string|max:25',
            'description' => 'required|string|max:255',
            'difficulty' => 'required|integer|between:1,5',
            'youtube' => ['required', 'active_url', new YoutubeLink],
            /*'video' => 'required_without:youtube|mimes:mp4,mov,mpg,mpeg|max:40000',*/
            'thumbnail' => 'mimes:jpg,jpeg,png|max:300',
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
            /*'youtube.required_without' => 'You must provide either a Youtube link or video file',
            'video.required_without' => 'You must provide either a video file or Youtube link',
            'video.max' => 'The video must be less than 40MB',*/
        ];
    }
}
