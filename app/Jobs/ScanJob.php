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

class ScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scan;
    protected $name;
    protected $scanner_url;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $name, string $scanner_url, Scan $scan)
    {
        $this->scan = $scan;
        $this->name = $name;
        $this->scanner_url = $scanner_url;
        error_log($name);
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
            'scanner_type' => $this->name,
        ]);

        $callbackUrl = route('callback', [ 'scanId' => $scanResult->id ]);

        $client = new Client();
        $request = new Request('POST', $this->scanner_url, [], \GuzzleHttp\json_encode([
                'url' => $this->scan->url,
                'callbackurls' => [$callbackUrl]
        ]));

        $response = $client->send($request);
    }

}
