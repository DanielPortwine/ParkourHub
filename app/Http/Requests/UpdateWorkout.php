<?php

namespace App\Http\Requests;

use App\Rules\Visibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateWorkout extends FormRequest
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
            'delete' => 'sometimes',
            'redirect' => 'sometimes|url',
        ];
    }
}
