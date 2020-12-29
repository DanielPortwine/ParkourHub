<?php

namespace App\Http\Requests;

use App\Rules\Visibility;
use App\Rules\YoutubeLink;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMovement extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth()->user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium');
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
            'description' => 'required|string|max:255',
            'youtube' => ['nullable', 'active_url', new YoutubeLink],
            'video' => 'mimes:mp4,mov,mpg,mpeg|max:500000',
            'fields' => 'required|array',
            'visibility' => ['required', new Visibility],
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
        ];
    }
}
