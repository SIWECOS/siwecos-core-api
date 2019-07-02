<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Scan;
use App\Jobs\StartScannerJob;

class StartOnlySpecificScannersTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        config([
            'siwecos.scanners.DOMXSS' => 'http://domxss-scanner/api/v1/domxss',
            'siwecos.scanners.INI_S' => 'http://ini-s-scanner/',
            'siwecos.scanners.INFOLEAK' => 'http://infoleak-scanner/',
            'siwecos.scanners.HEADER' => 'http://header-scanner/api/v1/header',
            'siwecos.scanners.TLS' => 'http://tls-scanner/start',
        ]);
    }

    /** @test */
    public function the_start_scan_route_allows_an_optional_scanners_parameter()
    {
        $response = $this->json('POST', '/api/v2/scan', [
            'url' => 'https://example.org',
            'callbackurls' => [
                'http://127.0.0.1'
            ],
            'dangerLevel' => 10,
            'scanners' => [
                'TLS', 'HEADER', 'INI_S'
            ]
        ]);

        $response->assertStatus(200);
        $this->assertCount(1, Scan::all());
        Queue::assertPushed(StartScannerJob::class, 3);
    }

    /** @test */
    public function if_a_not_configured_scanner_is_requested_a_proper_error_message_will_be_returned()
    {
        $response = $this->json('POST', '/api/v2/scan', [
            'url' => 'https://example.org',
            'callbackurls' => [
                'http://127.0.0.1'
            ],
            'dangerLevel' => 10,
            'scanners' => [
                'TLS', 'HEADER', 'INI_S', 'NOT_EXISTING'
            ]
        ]);

        $response->assertStatus(422);
        Queue::assertNothingPushed();
    }

    /** @test */
    public function if_an_invalid_scanners_parameter_is_sent_a_proper_error_message_will_be_returned()
    {
        $response = $this->json('POST', '/api/v2/scan', [
            'url' => 'https://example.org',
            'callbackurls' => [
                'http://127.0.0.1'
            ],
            'dangerLevel' => 10,
            'scanners' => [
                true
            ]
        ]);

        $response->assertStatus(422);
        Queue::assertNothingPushed();
    }
}
