<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\NotifyCallbacksJob;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use TiMacDonald\Log\LogFake;
use Illuminate\Support\Str;
use App\Scan;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class NotifyCallbacksJobTest extends TestCase
{
    use RefreshDatabase;

    public $scan;

    public function setUp(): void
    {
        parent::setUp();

        Log::swap(new LogFake);
        $this->scan = $this->generateScanWithResult();
        $this->scan->update(['finished_at' => now()]);
    }

    /** @test */
    public function the_notifyCallbackurlsJob_will_send_the_scans_results_to_the_given_callbackurls()
    {
        $this->assertCount(1, Scan::all());

        $job = new NotifyCallbacksJob($this->scan, $this->getMockedHttpClient([
            new Response(200)
        ]));

        $job->handle();

        Log::assertLogged('info', function ($message) {
            return Str::contains($message, 'Scan results for Scan ID ' . $this->scan->id . ' successfully sent to: ' . $this->scan->callbackurls[0]);
        });
        $this->assertCount(0, Scan::all());

        Log::assertLogged('info', function ($message) {
            return Str::contains($message, 'Scan with ID ' . $this->scan->id . ' finished successfully.');
        });
    }

    /** @test */
    public function if_a_callbackurl_was_not_reachable_a_proper_message_will_be_logged()
    {
        $job = new NotifyCallbacksJob($this->scan, $this->getMockedHttpClient([
            new Response(500)
        ]));

        $job->handle();

        Log::assertLogged('warning', function ($message) {
            return Str::contains($message, 'Scan results for Scan ID ' . $this->scan->id . ' could not be sent to: ' . $this->scan->callbackurls[0]);
        });
        $this->assertCount(1, Scan::all());

        Log::assertLogged('critical', function ($message) {
            return Str::contains($message, 'Scan with ID ' . $this->scan->id . ' could not be sent to any given callbackurls.');
        });
    }

    /** @test */
    public function if_a_curl_exception_occures_it_will_be_logged()
    {
        $job = new NotifyCallbacksJob($this->scan, $this->getMockedHttpClient([
            new RequestException("Error Communicating with Server", new Request('POST', 'test'))
        ]));

        $job->handle();

        Log::assertLogged('critical', function ($message) {
            return Str::contains($message, 'Scan results for Scan ID ' . $this->scan->id . ' could not be sent to: ' . $this->scan->callbackurls[0]);
        });
        $this->assertCount(1, Scan::all());

        Log::assertLogged('critical', function ($message) {
            return Str::contains($message, 'Error Communicating with Server');
        });
    }

    /** @test */
    public function all_successful_http_status_codes_are_handled_as_success()
    {
        $scan = factory(Scan::class)->create([
            'callbackurls' => [
                'http://callback.one',
                'http://callback.two',
                'http://callback.three',
            ]
        ]);

        $job = new NotifyCallbacksJob($scan, $this->getMockedHttpClient([
            new Response(200),
            new Response(201),
            new Response(202),
        ]));

        $job->handle();

        Log::assertLogged('info', function ($message) use ($scan) {
            return Str::contains($message, 'Scan results for Scan ID ' . $scan->id . ' successfully sent to: ');
        }, 3);
    }
}
