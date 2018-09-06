<?php

namespace App\Rules;
use App\Domain;
use GuzzleHttp\Client;
use Illuminate\Contracts\Validation\Rule;

class AnAvailableUrlExistsForTheDomain implements Rule
{

    protected $errorMessage = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        // TODO: Inject mocked Guzzle\Client to getDomainURL to test these responses here.
        $result = Domain::getDomainURL($value);
        // Domain is available with http:// or https://
        if( is_string($result) ) {
            return true;
        }

        // Domain is only available with or without www.
        elseif ( isset($result) && $result->isNotEmpty() ) {
            $this->errorMessage = $result->get('notAvailable') . " is not available. Did you mean " . $result->get('alternativeAvailable') . "?";
            return false;
        }

        $this->errorMessage = $value . " is not available.";
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }
}
