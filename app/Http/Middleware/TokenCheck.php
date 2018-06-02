<?php

namespace App\Http\Middleware;

use App\Token;
use Closure;

class TokenCheck
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('siwecosToken');
        if (isset($token)) {
            $headerToken = Token::getTokenByString($request->header('siwecosToken'));
            if ($headerToken instanceof Token) {
                return $next($request);
            }
        }

        return response('Token not allowed', 403);
    }
}
