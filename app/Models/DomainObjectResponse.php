<?php
/**
 * Created by PhpStorm.
 * User: marcelwege
 * Date: 23.11.17
 * Time: 19:04.
 */

namespace App\Siweocs\Models;

use App\Domain;

class DomainObjectResponse
{
    /**
     * @var string
     */
    public $domain;
    /**
     * @var bool
     */
    public $verificationStatus;
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $domainToken;

    public function __construct(Domain $domain)
    {
        $this->id = $domain->id;
        $this->domain = $domain->domain;
        $this->verificationStatus = (bool) $domain->verified;
        $this->domainToken = $domain->domain_token;
    }
}
