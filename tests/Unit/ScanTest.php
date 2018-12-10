<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Scan;

class ScanTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(){
        parent::setUp();

        $this->loadTestingEnvForAvailableScanners();
    }

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

        $this->assertEquals(50, Scan::first()->getTotalScore());

        $scan->results()->create([
            'result' => 'success',
            'scanner_type' => 'testing2',
            'total_score' => 100,
        ]);

        $this->assertEquals(100, Scan::first()->getTotalScore());
    }

    /** @test */
    public function the_correct_amount_of_available_scanners_get_calculated_when_two_are_set()
    {
        $this->assertEquals(2, Scan::getAvailableScanners()->count());
    }

    /** @test */
    public function the_url_and_the_name_can_be_extracted_from_availableScanners_method()
    {

        $this->assertEquals(2, Scan::getAvailableScanners()->count());
        $this->assertEquals(['name' => 'DOMXSS', 'url' => 'http://domxss-scanner'], Scan::getAvailableScanners()[0]);
        $this->assertEquals(['name' => 'INFOLEAK', 'url' => 'http://infoleak-scanner'], Scan::getAvailableScanners()[1]);
    }

    protected function loadTestingEnvForAvailableScanners() {
        $dotenv = new \Dotenv\Dotenv(__DIR__ . "/env-testing-2");
        $dotenv->overload();
    }

}
