<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Keygen\Keygen;
use Log;

const METATAGNAME = 'siwecostoken';

/**
 * App\Domain.
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $domain
 * @property string $domain_token
 * @property int|null $token_id
 * @property int $verified
 * @property-read \App\Token|null $token
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereCreatedAt( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereDomain( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereDomainToken( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereTokenId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereUpdatedAt( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereVerified( $value )
 * @mixin \Eloquent
 *
 * @property \Carbon\Carbon|null $last_notification
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Scan[] $scans
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereLastNotification( $value )
 */
class Domain extends Model
{
    protected $fillable = ['domain', 'token_id', 'verified', 'domain_token'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (array_key_exists('domain', $attributes)) {
            $domainFilter = parse_url($attributes['domain']);
            $this->domain = $domainFilter['scheme'].'://'.$domainFilter['host'];
        }
        if (array_key_exists('token', $attributes)) {
            $token = Token::getTokenByString($attributes['token']);
            $this->token_id = $token->id;
            $this->domain_token = Keygen::alphanum(64)->generate();
        }
    }

    /**
     * @return bool
     */
    public function checkMetatags()
    {
        try {
            ini_set('user_agent', 'Mozilla/4.0 (compatible; MSIE 6.0)');
            $tags = get_meta_tags($this->domain);
            foreach ($tags as $tagkey => $tagvalue) {
                if ($tagkey == METATAGNAME) {
                    if ($tagvalue == $this->domain_token) {
                        /*Hooray site is activated*/
                        $this->verified = 1;
                        $this->save();

                        return true;
                    }
                }
            }
        } catch (\Exception $exception) {
	        Log::warning($exception->getMessage());
        }

        return false;
    }

    public function token()
    {
        return $this->belongsTo(Token::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scans()
    {
        return $this->hasMany(Scan::class, 'url', 'domain');
    }

    /**
     * @return bool
     */
    public function checkHtmlPage()
    {
        /*get the content of the page. there should be nothing, except the activationkey*/
        ini_set('user_agent', 'Mozilla/4.0 (compatible; MSIE 6.0)');
        $url = $this->domain.'/'.$this->domain_token.'.html';

        try {
            $pageRequest = file_get_contents($url);
            if ($pageRequest == false) {
                return false;
            }
            if (strpos($pageRequest, $this->domain_token) !== false) {
                $this->verified = 1;
                $this->save();

                return true;
            }
        } catch (\Exception $exception) {
            Log::warning($exception->getMessage());
        }

        return false;
    }

    /**
     * @param string $domain
     * @param int    $tokenId
     *
     * @return Domain
     */
    public static function getDomainOrFail(string $domain, int $tokenId)
    {
        Log::warning('DOMAIN: '.$domain.' ID: '.$tokenId);

        $domain = self::where(['domain' => $domain, 'token_id' => $tokenId])->first();
        if ($domain instanceof self) {
            return $domain;
        }
    }
}
