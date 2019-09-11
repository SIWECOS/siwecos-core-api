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
        $scan = factory(Scan::class)->create();
        $result = $scan->results()->create(['scanner_code' => 'TLS']);

        $this->assertFalse($scan->isFinished);

        $result->update(['result' => json_decode(file_get_contents(base_path('tests/sampleScanResult.json')))]);

        $this->assertTrue($scan->refresh()->isFinished);
    }
}
