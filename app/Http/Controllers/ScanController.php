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
        $availableScanners = collect(config('siwecos.scanners'))->filter();
        $requestedScanners = collect($request->json('scanners'));

        $scan = Scan::create($request->validated());

        foreach ($availableScanners as $scanner_code => $url) {
            // Skip non-requested scanners
            if ($requestedScanners->isNotEmpty() && !$requestedScanners->contains($scanner_code)) {
                continue;
            }

            $scanResult = $scan->results()->create([
                'scanner_code' => $scanner_code
            ]);

            $this->dispatch(new StartScannerJob($scanResult));
        }
    }

    public function callback(ScanResult $result, Request $request)
    {
        $result->update([
            'result' => $request->json()->all(),
        ]);

        if ($result->scan->isFinished === true) {
            $result->scan->update(['finished_at' => now()]);
            $this->dispatch(new NotifyCallbacksJob($result->scan));
        }
    }
}
