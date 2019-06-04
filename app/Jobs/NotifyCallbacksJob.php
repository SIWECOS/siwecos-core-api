<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Scan;
use App\HTTPClient;
use App\Http\Responses\ScanCallbackResponse;

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
        $this->client = $client;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = $this->client ?: new HTTPClient();
        $hasAtLeastOneSuccessfulCallback = false;

        foreach ($this->scan->callbackurls as $callbackurl) {
            try {
                $response = $client->request('POST', $callbackurl, [
                    'json' => (new ScanCallbackResponse($this->scan))
                ]);

                if (in_array($response->getStatusCode(), [200, 201, 202])) {
                    \Log::info('Scan results for Scan ID ' . $this->scan->id . ' successfully sent to: ' . $callbackurl);
                    $hasAtLeastOneSuccessfulCallback = true;
                } else {
                    \Log::warning('Scan results for Scan ID ' . $this->scan->id . ' could not be sent to: ' . $callbackurl . PHP_EOL
                        . 'HTTP Status Code: ' . $response->getStatusCode());
                }
            } catch (\Exception $e) {
                \Log::critical('Scan results for Scan ID ' . $this->scan->id . ' could not be sent to: ' . $callbackurl . PHP_EOL
                    . 'The following Exception was thrown: ' . PHP_EOL . $e);
            }
        }

        if ($hasAtLeastOneSuccessfulCallback) {
            if ($this->scan->delete()) {
                \Log::info('Scan with ID ' . $this->scan->id . ' finished successfully.');
            }
        } else {
            \Log::critical('Scan with ID ' . $this->scan->id . ' could not be sent to any given callbackurls.');
        }
    }
}
