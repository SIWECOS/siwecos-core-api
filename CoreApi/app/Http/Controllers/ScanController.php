<?php

namespace App\Http\Controllers;

use App\Scan;
use Illuminate\Http\Request;
use App\Http\Requests\ScannerStartRequest;
use App\Jobs\ScanHeadersJob;
use App\Jobs\ScanDOMXSSJob;
use App\Jobs\ScanInfoLeakJob;
use App\Token;
use App\Domain;

class ScanController extends Controller
{
    public function start(ScannerStartRequest $request)
    {
        $token = Token::getTokenByString(($request->header('siwecosToken')));
        
        if ($token->reduceCredits() ) {

            // create a new scan order
            $scan = $token->scans()->create([
                'token_id' => $token->id,
                'url' => Domain::getDomainOrFail($request->get('url'), $token->id),
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
