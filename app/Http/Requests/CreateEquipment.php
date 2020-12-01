<?php

namespace App\Http\Requests;

use App\Rules\Checkbox;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateEquipment extends FormRequest
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
            'movement' => 'exists:App\Movement,id',
            'name' => 'required|string|max:25',
            'description' => 'required|string|max:255',
            'image' => 'required|mimes:jpg,jpeg,png|max:5000',
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
            'image.max' => 'The image must be less than 5MB',
        ];
    }
}
