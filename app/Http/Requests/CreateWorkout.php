<?php

namespace App\Http\Requests;

use App\Rules\Visibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateWorkout extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->isPremium();
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
            'description' => 'nullable|string|max:255',
            'movements' => 'required|array',
            'movements.*' => 'array:movement,fields',
            'movements.*.movement' => 'integer|exists:App\Models\Movement,id',
            'movements.*.fields' => 'array',
            'visibility' => ['required', new Visibility],
            'thumbnail' => 'mimes:jpg,jpeg,png|max:5000',
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
            'thumbnail.max' => 'The thumbnail must be less than 5MB',
        ];
    }
}
