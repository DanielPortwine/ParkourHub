<?php

namespace App\Http\Requests;

use App\Rules\Checkbox;
use App\Rules\Visibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateSpot extends FormRequest
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
        $imageMax = Auth::user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium') ? '5000' : '500';
        return [
            'name' => 'required|string|max:25',
            'description' => 'required|string|max:255',
            'image' => 'mimes:jpg,jpeg,png|max:' . $imageMax,
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
        $messages = [];
        if (Auth::user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium')) {
            $messages = [
                'image.max' => 'The image must be less than 5MB',
            ];
        }
        return $messages;
    }
}
