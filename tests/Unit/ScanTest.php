<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Scan;
use App\ScanResult;

class ScanTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function a_scan_can_have_many_scanResults()
    {
        $scan = factory(Scan::class)->create();
        $scan->results()->create(factory(ScanResult::class)->make()->toArray());

        $this->assertCount(1, Scan::all());
        $this->assertCount(1, $scan->results);
    }

    /** @test */
    public function a_scan_knows_if_some_of_its_results_had_an_error()
    {
        $scan = $this->generateScanWithResult();

        $this->assertFalse($scan->hasError);

        $this->addErrorResult($scan);
        $this->assertTrue($scan->refresh()->hasError);
    }

    /** @test */
    public function a_scan_know_if_its_finished()
    {
        config([
            'siwecos.scanners.INI_S' => 'http://ini-s-scanner',
            'siwecos.scanners.HEADER' => 'http://header-scanner/api/v1/header'
        ]);

        $scan = $this->generateScanWithResult();

        $this->assertFalse($scan->is_finished);
    }
}
