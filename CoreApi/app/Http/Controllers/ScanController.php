<?php

namespace App\Http\Controllers;

use App\Scan;
use App\Jobs\ScanHeadersJob;
use Illuminate\Http\Request;
use App\Http\Requests\ScannerStartRequest;

class ScanController extends Controller
{
    public function start(ScannerStartRequest $request)
    {
        // Todo: Reduce credits

        // create a new scan order
        $scan = Scan::create($request->all());

        // dispatch each scanner to the queue
        ScanHeadersJob::dispatch($scan);
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
