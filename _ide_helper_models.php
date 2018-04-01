<?php
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\Domain
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $domain
 * @property string $domain_token
 * @property int|null $token_id
 * @property int $verified
 * @property Scan[] scans
 * @property-read \App\Token|null $token
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereCreatedAt( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereDomain( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereDomainToken( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereTokenId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereUpdatedAt( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain whereVerified( $value )
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Scan[] $scans
 */
	class Domain extends \Eloquent {}
}

namespace App{
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Scan[] $scans
 */
	class Token extends \Eloquent {}
}

namespace App{
/**
 * App\ScanResult
 *
 * @property int $id
 * @property int $scan_id
 * @property string $scanner_type
 * @property \Illuminate\Support\Collection $result
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Scan $scan
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereScanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereScannerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScanResult whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class ScanResult extends \Eloquent {}
}

namespace App{
/**
 * App\Scan
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ScanResult[] $results
 * @property-read \App\Token $token
 * @property string $url
 * @property int|null $dangerLevel
 * @property \Illuminate\Support\Collection $callbackurls
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereCallbackurls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereDangerLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereUrl($value)
 * @property int $token_id
 * @property int $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereTokenId($value)
 * @property int $freescan
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Scan whereFreescan($value)
 */
	class Scan extends \Eloquent {}
}

