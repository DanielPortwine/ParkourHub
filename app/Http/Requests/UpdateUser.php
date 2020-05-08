<?php

namespace App\Http\Requests;

use App\Rules\Checkbox;
use App\Rules\UniqueOrOldEmail;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUser extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255', new UniqueOrOldEmail],
            'subscribed' => new Checkbox,
        ];
    }
}
