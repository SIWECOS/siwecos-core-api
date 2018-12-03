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
    public function the_correct_amount_of_available_scanners_get_calculated()
    {
        $this->assertEquals(0, Scan::getAvailableScanners()->count());

        config(['siwecos.available_scanners.WS_TLS' => 'http://test-scanner']);
        $this->assertEquals(1, Scan::getAvailableScanners()->count());

        config(['siwecos.available_scanners.INI_S' => 'http://test-scanner']);
        $this->assertEquals(2, Scan::getAvailableScanners()->count());

        config(['siwecos.available_scanners.INFOLEAK' => 'http://test-scanner']);
        $this->assertEquals(3, Scan::getAvailableScanners()->count());

        config(['siwecos.available_scanners.DOMXSS' => 'http://test-scanner']);
        $this->assertEquals(4, Scan::getAvailableScanners()->count());

        config(['siwecos.available_scanners.HEADER' => 'http://test-scanner']);
        $this->assertEquals(5, Scan::getAvailableScanners()->count());
    }

    /** @test */
    public function all_possible_scanners_are_set_within_the_siwecos_config_file() {
        $configAmount = count(config('siwecos.available_scanners'));

        // Available Scanners
        config(['siwecos.available_scanners.WS_TLS' => 'http://test-scanner']);
        config(['siwecos.available_scanners.INI_S' => 'http://test-scanner']);
        config(['siwecos.available_scanners.INFOLEAK' => 'http://test-scanner']);
        config(['siwecos.available_scanners.DOMXSS' => 'http://test-scanner']);
        config(['siwecos.available_scanners.HEADER' => 'http://test-scanner']);

        $this->assertEquals($configAmount, Scan::getAvailableScanners()->count());
    }
}
