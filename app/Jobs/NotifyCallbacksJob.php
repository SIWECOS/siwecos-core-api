<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Scan;
use App\HTTPClient;
use Illuminate\Support\Facades\Log;

class NotifyCallbacksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $scan;
    public $client;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Scan $scan, HTTPClient $client = null)
    {
        $this->scan = $scan;
        $this->client = $client ?: new HTTPClient();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $hasAtLeastOneSuccessfulCallback = false;

        foreach ($this->scan->callbackurls as $callbackurl) {
            $response = $this->client->request('POST', $callbackurl, ['json' => [
                $this->scan->results
            ]]);

            if ($response->getStatusCode() === 200) {
                Log::info('Scan results for Scan ID ' . $this->scan->id . ' successfully sent to: ' . $this->scan->callbackurls[0]);
                $hasAtLeastOneSuccessfulCallback = true;
            } else {
                Log::warning('Scan results for Scan ID ' . $this->scan->id . ' could not be sent to: ' . $this->scan->callbackurls[0]);
            }
        }

        if ($hasAtLeastOneSuccessfulCallback) {
            if ($this->scan->delete()) {
                Log::info('Scan with ID ' . $this->scan->id . ' finished successfully.');
            }
        } else {
            Log::critical('Scan with ID ' . $this->scan->id . ' could not be sent to any given callbackurls.');
        }
    }
}
