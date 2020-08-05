<?php

namespace App\Http\Requests;

use App\Rules\YoutubeLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateMovement extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'required|integer',
            'spot' => 'required_without_all:progression,advancement,move|integer|exists:App\Spot,id',
            'progression' => 'required_without_all:spot,advancement,move|integer|exists:App\Movement,id',
            'advancement' => 'required_without_all:spot,progression,move|integer|exists:App\Movement,id',
            'move' => 'required_without_all:spot,progression,advancement|integer|exists:App\Movement,id',
            'category' => 'required|integer',
            'name' => 'required|string|max:25',
            'description' => 'required|string|max:255',
            'youtube' => ['required_without:video', 'nullable', 'active_url', new YoutubeLink],
            'video' => 'required_without:youtube|mimes:mp4,mov,mpg,mpeg|max:500000',
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
            'youtube.required_without' => 'You must provide either a Youtube link or video file',
            'video.required_without' => 'You must provide either a video file or Youtube link',
            'video.max' => 'The video must be less than 500MB',
        ];
    }
}
