<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Keygen\Keygen;

/**
 * App\Domain
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $domain
 * @property int $token_id
 * @property int $verified
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereTokenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereVerified($value)
 * @property string $domain_token
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereDomainToken($value)
 */
class Domain extends Model
{
    protected $fillable = ['domain', 'token_id', 'verified', 'domain_token'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (array_key_exists('token', $attributes))
        {
            $token = Token::getTokenByString($attributes['token']);
            $this->token_id = $token->id;
            $this->domain_token = Keygen::token(24)->generate();
        }

    }

    public static function getDomain(string $domain, int $tokenId)
    {
        return Domain::where(['domain' => $domain, 'token_id' => $tokenId])->first();
    }

    public function deleteDomain(){

    }
}
