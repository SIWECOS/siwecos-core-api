<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScannerStartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // TODO: Check if thoken is authorized and have enough Credits.
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
            'domain' => 'required|url',
            'dangerLevel' => 'integer|min:0|max:10',
            'callbackurls' => 'array',
            'callbackurls.*' => 'url'
        ];
    }
}
