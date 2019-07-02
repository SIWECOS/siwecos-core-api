<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IsConfiguredScannerRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $availableScanners = collect(config('siwecos.scanners'))->filter();


        if (is_string($value) && $availableScanners->has($value)) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'One or more of the requested scanners are not configured.';
    }
}
