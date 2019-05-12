<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\StartScannerJob;
use App\Scan;
use Illuminate\Support\Facades\Queue;
use GuzzleHttp\Psr7\Response;
use App\ScanResult;
use Illuminate\Support\Facades\Log;
use TiMacDonald\Log\LogFake;
use Illuminate\Support\Str;

class StartScannerJobTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Log::swap(new LogFake);

        config([
            'siwecos.scanners.DOMXSS' => 'http://domxss-scanner/api/v1/domxss',
            'siwecos.scanners.INI_S' => 'http://ini-s-scanner/',
            'siwecos.scanners.INFOLEAK' => 'http://infoleak-scanner/',
            'siwecos.scanners.HEADER' => 'http://header-scanner/api/v1/header',
            'siwecos.scanners.TLS' => 'http://tls-scanner/start',
        ]);
    }

    /** @test */
    public function a_scanner_can_be_started()
    {
        $scan = factory(Scan::class)->create();

        $job = new StartScannerJob($scan, 'TLS', 'http://tls-scanner/start', $this->getMockedHttpClient([
            new Response(200)
        ]));
        $job->handle();

        $this->assertCount(1, ScanResult::all());
        $this->assertFalse($scan->hasError);
        Log::assertLogged('info', function ($message) {
            return Str::contains($message, 'Scan successful started');
        });
    }

    /** @test */
    public function if_a_scanner_could_not_be_started_a_critical_log_message_will_be_logged()
    {
        $scan = factory(Scan::class)->create();

        (new StartScannerJob($scan, 'TLS', 'http://tls-scanner/start', $this->getMockedHttpClient([
            new Response(500)
        ])))->handle();

        Log::assertLogged('critical', function ($message) {
            return Str::contains($message, 'Failed to start scan');
        });
        $this->assertTrue($scan->hasError);
    }

    /** @test */
    public function all_successful_http_status_codes_are_handled_as_success()
    {
        $scan = factory(Scan::class)->create();

        (new StartScannerJob($scan, 'testscanner', 'http://testscanner', $this->getMockedHttpClient([
            new Response(200)
        ])))->handle();
        (new StartScannerJob($scan, 'testscanner', 'http://testscanner', $this->getMockedHttpClient([
            new Response(201)
        ])))->handle();
        (new StartScannerJob($scan, 'testscanner', 'http://testscanner', $this->getMockedHttpClient([
            new Response(202)
        ])))->handle();

        Log::assertLogged('info', function ($message) {
            return Str::contains($message, 'Scan successful started');
        }, 3);
    }
}
