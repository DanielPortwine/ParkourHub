<?php

namespace App\Http\Requests;

use App\Rules\Checkbox;
use App\Rules\Hometown;
use App\Rules\NotAutoUsername;
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
            'name' => ['sometimes', 'string', 'max:25', new NotAutoUsername],
            'email' => ['required_with:account_form', 'string', 'email', 'max:255', new UniqueOrOldEmail],
            'hometown' => ['string', 'max:255', new Hometown],
            'subscribed' => new Checkbox,
        ];
    }
}
