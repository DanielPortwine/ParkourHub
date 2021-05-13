<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AddWorkoutToPlan extends FormRequest
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
            'workout' => 'required|exists:App\Models\Workout,id',
            'date' => 'required|date',
            'repeat_frequency' => 'required_with:repeat_until',
            'repeat_until' => 'nullable|required_with:repeat_frequency|after:date',
        ];
    }
}
