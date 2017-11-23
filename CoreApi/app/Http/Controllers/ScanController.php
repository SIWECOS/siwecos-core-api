<?php

namespace App\Http\Controllers;

use App\Scan;
use Illuminate\Http\Request;
use App\Http\Requests\ScannerStartRequest;
use App\Jobs\ScanHeadersJob;
use App\Jobs\ScanDOMXSSJob;
use App\Jobs\ScanInfoLeakJob;
use App\Token;

class ScanController extends Controller
{
    public function start(ScannerStartRequest $request)
    {
        $token = Token::whereToken($request->header('siwecosToken'))->first();
        
        if ($token->reduceCredits() ) {

            // create a new scan order
            $scan = $token->scan()->create([
                'token_id' => $token->id,
                'url' => $request->get('url'),
                'callbackurls' => $request->get('callbackurls'),
                'dangerLevel' => $request->get('dangerLevel')
            ]);

            // dispatch each scanner to the queue
            ScanHeadersJob::dispatch($scan);
            ScanDOMXSSJob::dispatch($scan);
            ScanInfoLeakJob::dispatch($scan);
            // Todo: dispatch TLS-Scanner

        }
    }

    public function status(Request $request)
    {
        // to be implemented
    }

    public function result(Request $request)
    {
        // to be implemented
    }

    public function resultRaw(Request $request)
    {
        // to be implemented
    }
}
