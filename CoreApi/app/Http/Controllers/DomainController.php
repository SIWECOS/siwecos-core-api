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
        $newdomain = new Domain(['domain' => $request->json('domain'), 'token' => $request->header('siwecosToken')]);
        try{
            $newdomain->save();
            return response()->json(new DomainAddResponse($newdomain));
        }
        catch (QueryException $queryException){
            return response($queryException->getMessage(), 500);
        }
    }

    public function verify(Request $request)
    {

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
        $domain = Domain::getDomain($request->json('domain'), $token->id);
        if ($domain instanceof Domain)
        {
            $domain->delete();
            return response()->json(new SiwecosBaseReponse('Domain removed'));
        }
        return response('Domain not found or no matching token', 404);
    }
}
