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
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

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
        $scanResult = factory(Scan::class)->create()->results()->create(['scanner_code' => 'TLS']);

        $job = new StartScannerJob($scanResult, $this->getMockedHttpClient([
            new Response(200)
        ]));
        $job->handle();

        $this->assertCount(1, ScanResult::all());
        $this->assertFalse($scanResult->scan->hasError);
        Log::assertLogged('info', function ($message) {
            return Str::contains($message, 'Scan successful started');
        });
    }

    /** @test */
    public function if_a_scanner_could_not_be_started_a_critical_log_message_will_be_logged()
    {
        $scanResult = factory(Scan::class)->create()->results()->create(['scanner_code' => 'TLS']);

        (new StartScannerJob($scanResult, $this->getMockedHttpClient([
            new Response(500)
        ])))->handle();

        Log::assertLogged('critical', function ($message) {
            return Str::contains($message, 'Failed to start scan');
        });
        $this->assertTrue(ScanResult::first()->has_error);
        $this->assertTrue($scanResult->scan->has_error);
    }

    /** @test */
    public function if_a_curl_exception_occurs_it_will_be_logged_and_the_scanResult_will_be_marked_as_failed()
    {
        $scan = factory(Scan::class)->create();

        (new StartScannerJob($scan->results()->create(['scanner_code' => 'TLS']), $this->getMockedHttpClient([
            new RequestException("Error Communicating with Server", new Request('POST', 'test'))
        ])))->handle();

        Log::assertLogged('critical', function ($message) {
            return Str::contains($message, 'Failed to start scan');
        });

        $this->assertTrue(ScanResult::first()->is_failed);
    }

    /** @test */
    public function all_successful_http_status_codes_are_handled_as_success()
    {
        $scanResult = factory(Scan::class)->create()->results()->create(['scanner_code' => 'TLS']);

        (new StartScannerJob($scanResult, $this->getMockedHttpClient([
            new Response(200)
        ])))->handle();
        (new StartScannerJob($scanResult, $this->getMockedHttpClient([
            new Response(201)
        ])))->handle();
        (new StartScannerJob($scanResult, $this->getMockedHttpClient([
            new Response(202)
        ])))->handle();

        Log::assertLogged('info', function ($message) {
            return Str::contains($message, 'Scan successful started');
        }, 3);
    }
}
