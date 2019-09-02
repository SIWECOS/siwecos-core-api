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
    public function a_scanResult_has_an_isFinished_parameter()
    {
        $this->generateScanWithResult([
            'result' => null
        ]);
        $result = ScanResult::find(1);
        $this->assertFalse($result->isFinished);

        $this->generateScanWithResult();
        $result = ScanResult::find(2);
        $this->assertTrue($result->isFinished);

        $this->generateScanWithResult([
            'result' => null,
            'is_failed' => true
        ]);

        $result = ScanResult::find(3);
        $this->assertTrue($result->isFinished);
    }
}
