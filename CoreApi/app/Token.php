<?php

namespace App;

use Doctrine\DBAL\Query\QueryException;
use Illuminate\Database\Eloquent\Model;
use Keygen\Keygen;
use App\Scan;
use App\Domain;

/**
 * Class Token
 *
 * @package App
 * @property int $id
 * @property string $token
 * @property integer $credits
 * @property boolean $active
 * @property int $acl_level
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Token whereAclLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Token whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Token whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Token whereCredits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Token whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Token whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Token whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Domain[] $domains
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Scan[] $scan
 */
class Token extends Model
{

    protected $fillable = ['credits', 'token'];

    protected $table = 'tokens';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        // Generate token by package gladcodes/keygen
        $this->token = Keygen::token(24)->generate();
    }

    /**
     * @param int $credits
     * @return bool
     */
    public function setTokenCredits(int $credits)
    {
        $this->credits = $credits;
        try {
            $this->save();
            return true;
        } catch (QueryException $queryException) {
            //TODO Log error to Papertrail with Token
            return false;
        }
    }

    public function setAclLevel(int $aclLevel)
    {
        $this->acl_level = $aclLevel;
    }

    public function reduceCredits($amount = 1)
    {
        $this->credits -= $amount;
        
        try{
            $this->save();
                return true;
        }
        catch (\Illuminate\Database\QueryException $queryException)
        {
            // TODO: Log error to Papertrail with Token
            return false;
        }
    }

    public function scans()
    {
        return $this->hasMany(Scan::class);
    }
    
    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public static function reduceToken(string $token, $amount = 1)
    {
        $token = self::getTokenByString($token);
        if ($token instanceof Token)
        {
            $token->credits -= $amount;
            try{
                $token->save();
                return true;
            }
            catch (\Illuminate\Database\QueryException $queryException)
            {
                //TODO Log error to Papertrail with Token
                return false;
            }

        }
    }

    public static function getTokenByString(string $token)
    {
        return Token::where('token', $token)->first();
    }


}
