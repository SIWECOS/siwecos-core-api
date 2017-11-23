<?php

namespace App\Http\Controllers;

use App\Scan;
use Illuminate\Http\Request;
use App\Http\Requests\ScannerStartRequest;
use App\Jobs\ScanHeadersJob;
use App\Jobs\ScanDOMXSSJob;
use App\Jobs\ScanInfoLeakJob;

class ScanController extends Controller
{
    public function start(ScannerStartRequest $request)
    {
        // Todo: Reduce credits

        // create a new scan order
        $scan = Scan::create([
            'token_id' => 7272,
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
