<?php

namespace App\Http\Middleware;

use App\Token;
use Closure;

class CreditCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $headerToken = Token::where('token', $request->header('siwecosToken'))->first();
        if ($headerToken instanceof Token)
        {
            if ($headerToken->credits > 0)
            {
                return $next($request);
            }
            return response('Not enough credits', 403);
        }
        return response('Token not allowed', 403);
    }
}
