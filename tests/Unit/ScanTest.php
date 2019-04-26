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

    // /** @test */
    // public function a_scan_knows_its_startedAt_and_finishedAt_times()
    // { }
}
