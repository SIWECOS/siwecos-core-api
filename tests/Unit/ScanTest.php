<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Scan;

class ScanTest extends TestCase
{
    /** @test */
    public function the_correct_amount_of_available_scanners_get_calculated()
    {
        $this->assertEquals(0, Scan::getAvailableScanners()->count());

        $dotenv = new \Dotenv\Dotenv(__DIR__ . "/env-testing-2");
        $dotenv->overload();

        $this->assertEquals(2, Scan::getAvailableScanners()->count());
    }

    /** @test */
    public function the_url_and_the_name_can_be_extracted_from_availableScanners_method()
    {
        $dotenv = new \Dotenv\Dotenv(__DIR__ . "/env-testing-2");
        $dotenv->overload();

        $this->assertEquals(2, Scan::getAvailableScanners()->count());
        $this->assertEquals(['name' => 'DOMXSS', 'url' => 'http://domxss-scanner'], Scan::getAvailableScanners()[0]);
        $this->assertEquals(['name' => 'INFOLEAK', 'url' => 'http://infoleak-scanner'], Scan::getAvailableScanners()[1]);
    }

}
