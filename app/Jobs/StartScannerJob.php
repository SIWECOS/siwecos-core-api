<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Scan;
use App\HTTPClient;
use App\ScanResult;

class StartScannerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $scanResult;
    public $client;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ScanResult $scanResult, HTTPClient $client = null)
    {
        $this->scanResult = $scanResult;
        $this->client = $client;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->scanResult->scan->started_at == null) {
            $this->scanResult->scan->update(['started_at' => now()]);
        }

        $client = $this->client ?: new HTTPClient();

        \Log::debug('Sending scan start request for scanner: ' . $this->scanResult->scanner_code);
        try {
            $response = $client->request('POST', config('siwecos.scanners')[$this->scanResult->scanner_code], ['json' => [
                'url' => $this->scanResult->scan->url,
                'dangerLevel' => $this->scanResult->scan->dangerLevel,
                'callbackurls' => [
                    config('app.url') . '/api/v2/callback/' . $this->scanResult->id
                ],
                'userAgent' => config('siwecos.userAgent')
            ]]);


            if (in_array($response->getStatusCode(), [200, 201, 202])) {
                \Log::info('Scan successful started for scanner ' . $this->scanResult->scanner_code);
            } else {
                \Log::critical('Failed to start scan for scanner ' . $this->scanResult->scanner_code);
                $this->scanResult->update([
                    'is_failed' => true
                ]);
            }
        } catch (\Exception $e) {
            \Log::critical('Failed to start scan for scanner ' . $this->scanResult->scanner_code . ' The following Exception was thrown: ' . $e);
            $this->scanResult->update([
                'is_failed' => true
            ]);
        }
    }
}
