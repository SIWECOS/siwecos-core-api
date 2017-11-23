<?php
/**
 * Created by PhpStorm.
 * User: marcelwege
 * Date: 23.11.17
 * Time: 18:13
 */

namespace App\Siweocs\Models;


use App\Domain;

/**
 * Class DomainAddResponse
 * @package App\Siweocs\Models
 */
class DomainAddResponse extends SiwecosBaseReponse
{

    /**
     * @var string
     */
    var $message;

    /**
     * @var boolean
     */
    var $verificationStatus;

    /**
     * @var integer
     */
    var $domainId;

    /**
     * @var string
     */
    var $domainToken;

    public function __construct(Domain $databaseDomain)
    {
        parent::__construct("Domain was created");
        $this->domainId = $databaseDomain->id;
        $this->verificationStatus = (bool)$databaseDomain->verified;
        $this->domainToken = $databaseDomain->domain_token;
    }
}