<?php
/**
 * Created by PhpStorm.
 * User: marcelwege
 * Date: 23.11.17
 * Time: 14:04
 */

namespace App\Siweocs\Models;


class AddTokenResponse extends SiwecosBaseReponse
{

    /**
     * @var string
     */
    var $token;

    /**
     * AddTokenResponse constructor.
     * @param String $token
     */
    public function __construct(String $token)
    {
        parent::__construct("token successful created");
        $this->token = $token;
    }
}