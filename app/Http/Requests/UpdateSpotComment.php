<?php

namespace App\Http\Requests;

use App\Rules\VideoImage;
use App\Rules\Visibility;
use App\Rules\YoutubeLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateSpotComment extends FormRequest
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
        if (Auth::user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium')) {
            $videoImage = [
                'video_image' => ['required_without_all:comment,youtube', 'nullable', 'mimes:jpg,jpeg,png,mp4,mov,mpg,mpeg', new VideoImage],
            ];
        } else {
            $videoImage = [
                'video_image' => 'required_without_all:comment,youtube|nullable|mimes:jpg,jpeg,png|max:500',
            ];
        }

        return array_merge([
            'comment' => 'required_without_all:youtube,video_image|nullable|string|max:255',
            'youtube' => ['required_without_all:comment,video_image', 'nullable', 'active_url', new YoutubeLink],
            'visibility' => ['required', new Visibility],
            'delete' => 'sometimes',
            'redirect' => 'sometimes|url',
        ], $videoImage);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $extraFileTypes = Auth::user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium') ? ', mp4, mov, mpg, mpeg' : '';
        return [
            'comment.required_without_all' => 'You must enter at least one of the fields',
            'youtube.required_without_all' => 'You must enter at least one of the fields',
            'video_image.required_without_all' => 'You must enter at least one of the fields',
            'video_image.mimes' => 'The file must be a file of type: jpg, jpeg, png' . $extraFileTypes,
        ];
    }
}
