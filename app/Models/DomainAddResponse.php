<?php
/**
 * Created by PhpStorm.
 * User: marcelwege
 * Date: 23.11.17
 * Time: 18:13.
 */

namespace App\Siweocs\Models;

use App\Domain;

/**
 * Class DomainAddResponse.
 */
class DomainAddResponse extends SiwecosBaseReponse
{
    /**
     * @var string
     */
    public $message;

    /**
     * @var bool
     */
    public $verificationStatus;

    /**
     * @var int
     */
    public $domainId;

    /**
     * @var string
     */
    public $domainToken;

    public function __construct(Domain $databaseDomain)
    {
        parent::__construct('Domain was created');
        $this->domainId = $databaseDomain->id;
        $this->verificationStatus = (bool) $databaseDomain->verified;
        $this->domainToken = $databaseDomain->domain_token;
    }
}
