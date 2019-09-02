<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Scan;
use App\Jobs\StartScannerJob;
use Illuminate\Support\Facades\Queue;

class ScanStartTest extends TestCase
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
    public function a_scan_can_be_started_if_the_correct_data_was_sent()
    {
        $this->withoutExceptionHandling();

        $response = $this->json('POST', '/api/v2/scan', [
            'url' => 'https://example.org',
            'callbackurls' => [
                'http://127.0.0.1'
            ],
            'dangerLevel' => 10
        ]);

        $response->assertStatus(200);
        $this->assertCount(1, Scan::all());
        Queue::assertPushed(StartScannerJob::class, 5);
    }

    /** @test */
    public function a_scan_can_not_be_started_if_invalid_data_was_sent()
    {
        $response = $this->json('POST', '/api/v2/scan');
        $response->assertStatus(422);

        $response = $this->json('POST', '/api/v2/scan', [
            'url' => 'not-a-url',
            'callbackurls' => [
                'http://127.0.0.1'
            ]
        ]);
        $response->assertStatus(422);

        $response = $this->json('POST', '/api/v2/scan', [
            'url' => 'https://example.org',
            // callbackurls missing
        ]);
        $response->assertStatus(422);

        $response = $this->json('POST', '/api/v2/scan', [
            'url' => 'https://example.org',
            'callbackurls' => [
                'not-a-url'
            ]
        ]);
        $response->assertStatus(422);

        $response = $this->json('POST', '/api/v2/scan', [
            'url' => 'https://example.org',
            'callbackurls' => [
                'http://127.0.0.1'
            ],
            'dangerLevel' => 1000 // > 10
        ]);
        $response->assertStatus(422);

        $response = $this->json('POST', '/api/v2/scan', [
            'url' => 'https://example.org',
            'callbackurls' => [
                'http://127.0.0.1'
            ],
            'dangerLevel' => -1 // < 0
        ]);
        $response->assertStatus(422);

        $response = $this->json('POST', '/api/v2/scan', [
            'url' => 'https://example.org',
            'callbackurls' => [
                'http://127.0.0.1'
            ],
            'dangerLevel' => 'ten' // string
        ]);
        $response->assertStatus(422);
    }
}
