<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Scan;

class ScanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_scan_can_calculate_its_total_score_based_on_the_scan_results() {

        $scan = Scan::create([
            'token_id' => 1,
            'url' => 'http://testdomain',
            'dangerLevel' => 0,
        ]);

        $scan->results()->create([
            'result' => 'success',
            'scanner_type' => 'testing',
            'total_score' => 100,
        ]);

        $this->assertEquals(20, Scan::first()->getTotalScore());

        $scan->results()->create([
            'result' => 'success',
            'scanner_type' => 'testing2',
            'total_score' => 100,
        ]);

        $this->assertEquals(40, Scan::first()->getTotalScore());
    }

    /** @test */
    public function the_correct_amount_of_available_scanners_get_calculated_when_five_are_set()
    {
        $this->assertEquals(5, Scan::getAvailableScanners()->count());
    }

    /** @test */
    public function the_url_and_the_name_can_be_extracted_from_availableScanners_method()
    {
        $this->assertEquals(['name' => 'DOMXSS', 'url' => 'http://header-domxss-scanner/api/v1/domxss'], Scan::getAvailableScanners()[1]);
        $this->assertEquals(['name' => 'INFOLEAK', 'url' => 'http://infoleak-scanner'], Scan::getAvailableScanners()[2]);
    }


}
