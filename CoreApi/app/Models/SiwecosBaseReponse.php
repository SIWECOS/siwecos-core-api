<?php
/**
 * Created by PhpStorm.
 * User: marcelwege
 * Date: 23.11.17
 * Time: 14:03
 */

namespace App\Siweocs\Models;


use Illuminate\Database\Eloquent\Model;

class SiwecosBaseReponse
{

    /**
     * @var boolean
     */
    var $hasFailed;

    /**
     * @var string
     */
    var $message;

    public function __construct(string $message = "", bool $hasFailed = false)
    {
        $this->message = $message;
        $this->hasFailed = $hasFailed;
    }

}