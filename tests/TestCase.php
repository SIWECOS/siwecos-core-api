<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Scan;
use App\ScanResult;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Generate and Return a sample Scan with an associated ScanResult
     *
     * @return Scan
     */
    protected function generateScanWithResult()
    {
        $scan = factory(Scan::class)->create();
        $scan->results()->create(factory(ScanResult::class)->make()->toArray());

        return $scan;
    }

    /**
     * Generate and Return a sample Scan with an associated ScanResult where hasError: true
     *
     * @return Scan
     */
    protected function generateScanWithErrorResult()
    {
        $scan = factory(Scan::class)->create();
        $scan->results()->create(factory(ScanResult::class)->make([
            'result' => json_decode(file_get_contents(base_path('tests/sampleHeaderErrorScanResult.json')))
        ])->toArray());

        return $scan;
    }

    /**
     * Add an errored result to the $scan
     *
     * @return Scan
     */
    protected function addErrorResult(Scan $scan)
    {
        $scan->results()->create(factory(ScanResult::class)->make([
            'result' => json_decode(file_get_contents(base_path('tests/sampleHeaderErrorScanResult.json')))
        ])->toArray());

        return $scan;
    }
}
