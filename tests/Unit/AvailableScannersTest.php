<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AvailableScannersTest extends TestCase
{
    /** @test */
    public function a_collection_with_available_scanners_can_be_retrieved_from_config()
    {
        $this->assertCount(8, config('siwecos.scanners'));

        // filter all empty values
        $availableScanners = array_filter(config('siwecos.scanners'));
        $this->assertCount(0, $availableScanners);

        config(['siwecos.scanners.HEADER' => 'http://header-scanner']);

        $availableScanners = array_filter(config('siwecos.scanners'));
        $this->assertCount(1, $availableScanners);
        $this->assertEquals('HEADER', key($availableScanners));
    }
}
