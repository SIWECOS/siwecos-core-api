<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CallbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // TODO: Maybe check, if request comes from trusted scanner IP / etc.
        // aka Firewall
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // TODO: Adjust this according to the swagger definition
        return [
            'status' => 'required|string',
            'result' => 'array',
        ];
    }
}
