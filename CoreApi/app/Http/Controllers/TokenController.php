<?php

namespace App\Http\Controllers;

use App\Http\Requests\TokenAddRequest;
use App\Http\Requests\TokenRequest;
use App\Http\Requests\TokenSetCreditRequest;
use App\Siweocs\Models\TokenAddResponse;
use App\Siweocs\Models\ErrorResponse;
use App\Siweocs\Models\SiwecosBaseReponse;
use App\Siweocs\Models\TokenStatusResponse;
use App\Token;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class TokenController extends Controller
{

    public function add(TokenAddRequest $request)
    {
        // Create new token instance
        $newToken = new Token(['credits' => $request->json('credits')]);
        if (array_key_exists('aclLevel', $request->json())){
            $newToken->setAclLevel($request->json('aclLevel'));
        }
        // Try insertion to database
        try {
            $newToken->save();
            return response()->json(new TokenAddResponse($newToken->token));
        } catch (QueryException $queryException) {
            // Query has failed
            return response($queryException->getMessage(), 500);
        }
    }

    public function revoke(TokenRequest $request)
    {
        // Get requested Token
        $requestedToken = Token::getTokenByString($request->json('token'));

        if ($requestedToken instanceof Token) {
            try {
                $requestedToken->delete();
                return response()->json(new SiwecosBaseReponse('Token revoked', false));
            } catch (QueryException $queryException) {
                return response()->json(new ErrorResponse($queryException->getMessage()));
            }
        }
        return response('Token not found',404);
    }

    public function status(TokenRequest $request)
    {
        $requestedToken = Token::getTokenByString($request->json('token'));
        if ($requestedToken instanceof Token) {
            return response()->json(new TokenStatusResponse($requestedToken));
        } else {
            return response('Token not found',404);
        }
    }

    public function setCredits(TokenSetCreditRequest $request)
    {
        $requestedToken = Token::getTokenByString($request->json('token'));
        $credits = $request->json('credits');
        if ($requestedToken instanceof Token) {
            if ($requestedToken->setTokenCredits($credits)) {
                return response()->json(new SiwecosBaseReponse('Token credits changed', false));
            }
            return response('Token credits NOT changed', 500);
        }
        return response('Token not found', 404);
    }
}
