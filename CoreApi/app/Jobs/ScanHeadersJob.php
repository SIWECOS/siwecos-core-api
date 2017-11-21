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

class ScanHeadersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scan;

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

        
    }

    public function runScan()
    {
        $client = new Client();
        $response = $client->get(env('HEADER_SCANNER_URL') . '/api/v1/header', [
            'query' => [
                'url' => $this->scan->url
            ]
        ]);
        
        $this->scan->results()->create([
            'scanner_type' => 'hsts',
            'result' => $response->getBody()
        ]);
    }
}
