<?php

namespace App\Http\Controllers;

use App\Domain;
use App\Http\Requests\DomainAddRequest;
use App\Siweocs\Models\DomainAddResponse;
use App\Siweocs\Models\DomainListResponse;
use App\Siweocs\Models\SiwecosBaseReponse;
use App\Token;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Keygen\Keygen;

class DomainController extends Controller {
	/**
	 * @param DomainAddRequest $request
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function add( DomainAddRequest $request ) {
		$domainFilter = parse_url( $request->get( 'domain' ) );
		$domain       = $domainFilter['scheme'] . '://' . $domainFilter['host'];

		/** @var Domain $exisitingDomain */
		$exisitingDomain = Domain::whereDomain( $domain )->first();
		if ( $exisitingDomain instanceof Domain ) {
			if ( $exisitingDomain->verified === 1 ) {
				return response( 'Domain already there', 500 );
			}
			/** @var Token $token */
			$token                         = Token::whereToken( $request->header( 'siwecosToken' ) )->first();
			$exisitingDomain->token_id     = $token->id;
			$exisitingDomain->domain_token = Keygen::alphanum( 64 )->generate();
			try {
				$exisitingDomain->save();

				return response()->json( new DomainAddResponse( $exisitingDomain ) );
			} catch ( QueryException $queryException ) {
				return response( $queryException->getMessage(), 500 );
			}
		}

		$newDomain = new Domain( [
			'domain' => $request->json( 'domain' ),
			'token'  => $request->header( 'siwecosToken' )
		] );
		try {
			$newDomain->save();

			return response()->json( new DomainAddResponse( $newDomain ) );
		} catch ( QueryException $queryException ) {
			return response( $queryException->getMessage(), 500 );
		}
	}

	public function verify( Request $request ) {
		$token  = Token::getTokenByString( $request->header( 'siwecosToken' ) );
		$domain = Domain::getDomainOrFail( $request->json( 'domain' ), $token->id );
		if ( ! $domain->checkHtmlPage() ) {
			if ( ! $domain->checkMetatags() ) {
				return response( "Page not validate", 417 );
			}
		}

		return response()->json( new SiwecosBaseReponse( 'Page successful validated' ) );
	}

	public function list( Request $request ) {
		$token   = Token::getTokenByString( $request->header( 'siwecosToken' ) );
		$domains = $token->domains()->get();

		return response()->json( new DomainListResponse( $domains ) );
	}

	public function remove( DomainAddRequest $request ) {
		$domainFilter = parse_url( $request->json( 'domain' ) );
		$domain       = $domainFilter['scheme'] . '://' . $domainFilter['host'];

		$token  = Token::getTokenByString( $request->header( 'siwecosToken' ) );
		$domain = Domain::getDomainOrFail( $domain, $token->id );
		try {
			$domain->delete();
		} catch ( \Exception $e ) {
			return response( $e->getMessage(), 500 );
		}

		return response()->json( new SiwecosBaseReponse( 'Domain removed' ) );
	}
}
