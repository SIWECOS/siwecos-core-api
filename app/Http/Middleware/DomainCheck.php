<?php

namespace App\Http\Middleware;

use App\Domain;
use App\Token;
use Closure;

class DomainCheck
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
        \Log::info('DomainCheck: ' . json_encode($request->json()->all()));
        $headerToken = Token::getTokenByString($request->header('siwecosToken'));
        \Log::info('TOKEN (middleware): ' . $headerToken->token);
        $domainCheck = Domain::getDomainOrFail($request->json('domain'), $headerToken->id);
         \Log::info('DomainFound: '. $domainCheck->domain);
        if ($domainCheck instanceof Domain)
        {
            if ((bool)$domainCheck->verified)
            {
                return $next($request);
            }
        }
        return response("Token for domain not valid or domain not validated");
    }
}
