<?php
/**
 * Created by PhpStorm.
 * User: marcelwege
 * Date: 23.11.17
 * Time: 14:08
 */

namespace App\Siweocs\Models;


class ErrorResponse extends SiwecosBaseReponse
{
    public function __construct(string $message)
    {
        parent::__construct($message, true);
    }
}