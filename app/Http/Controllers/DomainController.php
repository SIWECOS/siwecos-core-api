<?php

namespace App\Http\Controllers;

use App\Domain;
use App\Http\Requests\DomainAddRequest;
use App\Siweocs\Models\DomainAddResponse;
use App\Siweocs\Models\DomainListResponse;
use App\Siweocs\Models\SiwecosBaseReponse;
use App\Token;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function add(DomainAddRequest $request)
    {
    	$domainFilter = parse_url( $request->get('domain') );
		$domain = $domainFilter['scheme'] . '://' . $domainFilter['host'];

		if (Domain::whereDomain($domain)->first() instanceof Domain){
			return response('Domain already there', 500);
		}

        $newdomain = new Domain(['domain' => $request->json('domain'), 'token' => $request->header('siwecosToken')]);
        try {
            $newdomain->save();
            return response()->json(new DomainAddResponse($newdomain));
        } catch (QueryException $queryException) {
            return response($queryException->getMessage(), 500);
        }
    }

    public function verify(Request $request)
    {
        $token = Token::getTokenByString($request->header('siwecosToken'));
        $domain = Domain::getDomainOrFail($request->json('domain'), $token->id);
        if (!$domain->checkHtmlPage())
        {
            if (!$domain->checkMetatags())
            {
                return response("Page not validate", 417);
            }
        }
        return response()->json(new SiwecosBaseReponse('Page successful validated'));
    }

    public function list(Request $request)
    {
        $token = Token::getTokenByString($request->header('siwecosToken'));
        $domains = $token->domains()->get();
        return response()->json(new DomainListResponse($domains));
    }

    public function remove(DomainAddRequest $request)
    {
        $token = Token::getTokenByString($request->header('siwecosToken'));
        $domain = Domain::getDomainOrFail($request->json('domain'), $token->id);
        $domain->delete();
        return response()->json(new SiwecosBaseReponse('Domain removed'));
    }
}
