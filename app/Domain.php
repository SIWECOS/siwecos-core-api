<?php

namespace App;

use GuzzleHttp\Client;
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
    protected $client = null;

    public function __construct(array $attributes = [], Client $client = null)
    {
        parent::__construct($attributes);

        if (array_key_exists('domain', $attributes)) {
            $this->domain = $attributes['domain'];
        }
        if (array_key_exists('token', $attributes)) {
            $token = Token::getTokenByString($attributes['token']);
            $this->token_id = $token->id;
            $this->domain_token = Keygen::alphanum(64)->generate();
        }

        $this->client = $client;
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

    /**
     * Returns a valid URL for the given domain (hostname) that is reachable.
     *
     * @param string $domain Domain / Hostname to get the URL for.
     * @param Client $client Guzzle Client for PHPUnit testing only.
     *
     * @return string|Collection|null A valid URL incl. schema if valid. Collection with alternative URL if the given one was not valid or or NULL if no URL is available.
     */
    public static function getDomainURL(string $domain, Client $client = null)
    {
        $testDomain = $domain;

        // Pings via guzzle
        $client = $client ?: new Client();

        $scheme = parse_url($testDomain, PHP_URL_SCHEME);

        // if user entered a URL -> test if available
        if ($scheme) {
            try {
                $testURL = $testDomain;
                $response = $client->request('GET', $testURL, ['verify' => false]);
                if ($response->getStatusCode() === 200) {
                    return $testURL;
                }
            } catch (\Exception $e) {
                // if not available, remove scheme from domain
                // scheme = https; + 3 for ://
                $testDomain = substr($domain, strlen($scheme) + 3);
            }
        }

        // Domain is available via https://
        try {
            $testURL = 'https://'.$testDomain;
            $response = $client->request('GET', $testURL, ['verify' => false]);
            if ($response->getStatusCode() === 200) {
                return $testURL;
            }
        } catch (\Exception $e) {
        }

        // Domain is available via http://
        try {
            $testURL = 'http://'.$testDomain;
            $response = $client->request('GET', $testURL, ['verify' => false]);
            if ($response->getStatusCode() === 200) {
                return $testURL;
            }
        } catch (\Exception $e) {
        }

        // Domain is available with or without www
        // if www. is there, than remove it, otherwise add it
        $testDomain = substr($testDomain, 0, 4) === 'www.' ? substr($testDomain, 4) : 'www.'.$testDomain;

        try {
            $testURL = 'https://'.$testDomain;
            $response = $client->request('GET', $testURL, ['verify' => false]);
            if ($response->getStatusCode() === 200) {
                return collect([
                    'notAvailable'         => $domain,
                    'alternativeAvailable' => $testDomain,
                ]);
            }
        } catch (\Exception $e) {
        }

        try {
            $testURL = 'http://'.$testDomain;
            $response = $client->request('GET', $testURL, ['verify' => false]);
            if ($response->getStatusCode() === 200) {
                return collect([
                    'notAvailable'         => $domain,
                    'alternativeAvailable' => $testDomain,
                ]);
            }
        } catch (\Exception $e) {
        }
    }
}
