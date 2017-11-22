<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client;
use App\Scan;
use App\ScanResult;

class ScanDOMXSSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scan;
    protected $result;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->runScan();
        $this->notifyCallbacks();
    }

    
    /**
     * Runs the defined scan and stores the result in the database.
     *
     * @return void
     */
    public function runScan()
    {
        $client = new Client();
        $response = $client->get(env('DOMXSS_SCANNER_URL') . '/api/v1/domxss', [
            'query' => [
                'url' => $this->scan->url
            ]
        ]);
        
        $this->result = $this->scan->results()->create([
            'scanner_type' => 'xss',
            'result' => $response->getBody()
        ]);
    }

    /**
     * Sends the ScanResult to the given callback urls.
     *
     * @return void
     */
    public function notifyCallbacks()
    {
        foreach ($this->scan->callbackurls as $callback) {
            $client = new Client();
            $client->post($callback, [
                'http_errors' => false,
                'body' => $this->result,
            ]);
        }
    }
}
