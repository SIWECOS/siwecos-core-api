<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Scan;
use App\ScanResult;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function generateScanWithResult()
    {
        $scan = factory(Scan::class)->create();
        $scan->results()->create(factory(ScanResult::class)->make()->toArray());

        return $scan;
    }
}
