<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ScanStartRequest;
use App\Scan;
use App\Jobs\StartScannerJob;

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
}
