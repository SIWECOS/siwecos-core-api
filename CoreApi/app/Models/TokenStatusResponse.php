<?php
/**
 * Created by PhpStorm.
 * User: marcelwege
 * Date: 23.11.17
 * Time: 14:49
 */

namespace App\Siweocs\Models;


use App\Token;

class TokenStatusResponse extends SiwecosBaseReponse
{

    /**
     * @var integer
     */
    var $credits;

    /**
     * @var integer
     */
    var $aclLevel;

    /**
     * @var boolean
     */
    var $active;

    public function __construct(Token $databaseToken)
    {
        parent::__construct("current state of requested token");
        $this->active = $databaseToken->active;
        $this->aclLevel = $databaseToken->acl_level;
        $this->credits = $databaseToken->credits;
    }

}