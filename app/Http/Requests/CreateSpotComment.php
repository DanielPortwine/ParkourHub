<?php

namespace App\Http\Requests;

use App\Rules\YoutubeLink;
use Illuminate\Foundation\Http\FormRequest;

class CreateSpotComment extends FormRequest
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
            'comment' => 'required_without_all:youtube,video_image|nullable|string|max:255',
            'youtube' => ['required_without_all:comment,video_image', 'nullable', 'active_url', new YoutubeLink],
            'video_image' => 'required_without_all:comment,youtube|nullable|mimes:jpg,jpeg,png|max:400',
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
            'comment.required_without_all' => 'You must enter at least one of the fields',
            'youtube.required_without_all' => 'You must enter at least one of the fields',
            'video_image.required_without_all' => 'You must enter at least one of the fields',
            'video_image.mimes' => 'The image must be a file of type: jpg, jpeg, png.',
        ];
    }
}
