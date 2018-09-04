<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Rules\AnAvailableUrlExistsForTheDomain;
use Illuminate\Http\Exceptions\HttpResponseException;

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

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'domain'         => ['required', new AnAvailableUrlExistsForTheDomain],
            'dangerLevel'    => 'integer|min:0|max:10',
            'callbackurls'   => 'array',
            'callbackurls.*' => 'url',
        ];
    }
}
