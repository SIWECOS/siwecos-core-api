<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client;
use App\Scan;
use GuzzleHttp\Psr7\Request;

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
        $this->scan->update([
            'status' => 2
        ]);

        $scanResult = $this->scan->results()->create([
            'scanner_type' => 'hsts',
        ]);

        $callbackUrl = route('callback', [ 'scanId' => $scanResult->id ]);

        $client = new Client();
        $request = new Request('POST', env('HEADER_SCANNER_URL') . '/api/v1/header', ['connect_timeout' => 0.5], \GuzzleHttp\json_encode([
                'url' => $this->scan->url,
                'callbackurls' => [$callbackUrl]
        ]));

        $response = $client->sendAsync($request);
        $response->wait();
    }

}
