<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Rules\IsConfiguredScannerRule;

class ScanStartRequest extends FormRequest
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
     * Throw an exception with status code 422 instead of an error bag
     *
     * @param Validator $validator
     * @return void
     */
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
            'url' => ['required', 'string'],
            'callbackurls' => ['required', 'array'],
            'callbackurls.*' => ['url'],
            'dangerLevel' => ['required', 'integer', 'min:0', 'max:10'],
            'scanners' => ['array'],
            'scanners.*' => ['string', new IsConfiguredScannerRule()]
        ];
    }
}
