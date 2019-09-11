<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Scan;
use App\Jobs\NotifyCallbacksJob;

class ScanFinishedTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        config([
            'siwecos.scanners.INI_S' => 'http://ini-s-scanner',
            'siwecos.scanners.HEADER' => 'http://header-scanner/api/v1/header',
            'siwecos.scanners.TLS' => 'http://tls-scanner',
        ]);
    }

    /** @test */
    public function when_the_last_scanResult_was_retrieved_a_notifyCallbacksJob_gets_dispatched()
    {
        $scan = factory(Scan::class)->create();

        $scanResult1 = $scan->results()->create([
            'scanner_code' => 'INI_S'
        ]);

        $scanResult2 = $scan->results()->create([
            'scanner_code' => 'HEADER'
        ]);

        $scannerResponseJson = json_decode(file_get_contents(base_path('tests/sampleScanResult.json')));
        $response = $this->json('POST', '/api/v2/callback/' . $scanResult1->id, collect($scannerResponseJson)->toArray());

        $response->assertStatus(200);
        Queue::assertNotPushed(NotifyCallbacksJob::class);

        $scannerResponseJson = json_decode(file_get_contents(base_path('tests/sampleHeaderErrorScanResult.json')));
        $response = $this->json('POST', '/api/v2/callback/' . $scanResult2->id, collect($scannerResponseJson)->toArray());

        $response->assertStatus(200);
        Queue::assertPushed(NotifyCallbacksJob::class);
    }
}
