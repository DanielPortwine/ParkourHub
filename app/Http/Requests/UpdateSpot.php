<?php

namespace App\Http\Requests;

use App\Rules\Checkbox;
use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'name' => 'required|string|max:25',
            'description' => 'required|string|max:255',
            'image' => 'mimes:jpg,jpeg,png|max:500',
            'private' => new Checkbox,
        ];
    }
}
