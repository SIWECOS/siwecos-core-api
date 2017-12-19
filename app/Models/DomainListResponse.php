<?php
/**
 * Created by PhpStorm.
 * User: marcelwege
 * Date: 23.11.17
 * Time: 18:58
 */

namespace App\Siweocs\Models;


use App\Domain;
use Illuminate\Database\Eloquent\Collection;

class DomainListResponse extends SiwecosBaseReponse
{
    /**
     * @var array
     */
    var $domains;

    public function __construct(Collection $domains)
    {
        parent::__construct('List of all domains');
        $this->domains = array();
        /** @var Domain $domain */
        foreach ($domains as $domain)
        {
            array_push($this->domains, new DomainObjectResponse($domain));
        }
    }
}