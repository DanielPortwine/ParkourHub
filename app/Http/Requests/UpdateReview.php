<?php

namespace App\Http\Requests;

use App\Rules\Visibility;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReview extends FormRequest
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
            'rating' => 'required|integer|between:0,5',
            'title' => 'nullable|string|max:25',
            'review' => 'nullable|string|max:255',
            'visibility' => ['required', new Visibility],
            'delete' => 'sometimes',
            'redirect' => 'sometimes|url',
        ];
    }
}
