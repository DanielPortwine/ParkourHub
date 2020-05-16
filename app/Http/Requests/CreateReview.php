<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateReview extends FormRequest
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
            'spot' => 'required|integer|exists:App\Spot,id',
            'rating' => 'required|integer|between:0,5',
            'title' => 'nullable|required_with:review|string|max:25',
            'review' => 'nullable|string|max:255',
        ];
    }
}
