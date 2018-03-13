<?php

namespace App\Http\Middleware;

use App\Token;
use Closure;

class CreditCheck {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure $next
	 *
	 * @return mixed
	 */
	public function handle( $request, Closure $next ) {
		$headerToken = Token::getTokenByString( $request->header( 'siwecosToken' ) );
		if ( $headerToken instanceof Token ) {
			if ( $headerToken->credits > 0 ) {
				return $next( $request );
			}

			return response( 'Not enough credits', 452 );
		}

		return response( 'Token not allowed', 403 );
	}
}
