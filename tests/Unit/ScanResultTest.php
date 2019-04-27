<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Scan;
use App\ScanResult;

class ScanResultTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_scanResult_belongs_to_a_scan()
    {
        $this->generateScanWithResult();

        $this->assertEquals(Scan::first(), ScanResult::first()->scan);
    }

    /** @test */
    public function a_scanResult_knows_if_there_was_a_global_error()
    {
        $scan1 = $this->generateScanWithResult();
        $this->assertFalse($scan1->results->first()->hasError);

        $scan2 = $this->generateScanWithErrorResult();
        $this->assertTrue($scan2->results->first()->hasError);
    }
}
