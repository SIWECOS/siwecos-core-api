<?php
/**
 * Created by PhpStorm.
 * User: marcelwege
 * Date: 23.11.17
 * Time: 14:03.
 */

namespace App\Siweocs\Models;

class SiwecosBaseReponse
{
    /**
     * @var bool
     */
    public $hasFailed;

    /**
     * @var string
     */
    public $message;

    public function __construct(string $message = '', bool $hasFailed = false)
    {
        $this->message = $message;
        $this->hasFailed = $hasFailed;
    }
}
