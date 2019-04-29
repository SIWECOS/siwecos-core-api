<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ScanStartRequest;
use App\Scan;
use App\Jobs\StartScannerJob;
use App\ScanResult;
use App\Jobs\NotifyCallbacksJob;

class ScanController extends Controller
{
    public function start(ScanStartRequest $request)
    {
        $availableScanners = array_filter(config('siwecos.scanners'));

        $scan = Scan::create($request->validated());

        foreach ($availableScanners as $name => $url) {
            $this->dispatch(new StartScannerJob($scan, $name, $url));
        }
    }

    public function callback(ScanResult $result, Request $request)
    {
        $result->update([
            'result' => $request->json()->all(),
            'has_error' => $request->json('hasError'),
        ]);

        if ($result->scan->is_finished) {
            $this->dispatch(new NotifyCallbacksJob($result->scan));
        }
    }
}
