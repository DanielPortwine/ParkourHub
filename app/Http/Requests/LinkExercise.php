<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class LinkExercise extends FormRequest
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
            'move' => 'required|integer|exists:App\Movement,id',
            'exercise' => 'required|integer|exists:App\Movement,id',
        ];
    }
}
