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
        $this->generateScanWithResult();

        $this->assertFalse(ScanResult::first()->result->get('hasError'));
    }
}
