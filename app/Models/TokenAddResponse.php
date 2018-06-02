<?php
/**
 * Created by PhpStorm.
 * User: marcelwege
 * Date: 23.11.17
 * Time: 14:04.
 */

namespace App\Siweocs\Models;

class TokenAddResponse extends SiwecosBaseReponse
{
    /**
     * @var string
     */
    public $token;

    /**
     * AddTokenResponse constructor.
     *
     * @param string $token
     */
    public function __construct(String $token)
    {
        parent::__construct('token successful created');
        $this->token = $token;
    }
}
