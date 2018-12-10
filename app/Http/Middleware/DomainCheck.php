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
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $headerToken = Token::getTokenByString($request->header('siwecosToken'));
        $domainCheck = Domain::getDomainOrFail($request->json('domain'), $headerToken->id);

        if (!($domainCheck instanceof Domain)) {
            return response('Domain not found');
        }

        if ($domainCheck instanceof Domain) {
            if ((bool) $domainCheck->verified) {
                return $next($request);
            }
        }

        return response('Token for domain not valid or domain not validated');
    }
}
