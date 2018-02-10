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

class ScanInfoLeakJob implements ShouldQueue
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
            'scanner_type' => 'infoLeak',
        ]);

        $callbackUrl = route('callback', [ 'scanId' => $scanResult->id ]);

        $client = new Client();
        $request = new Request('POST', env('INFOLEAK_SCANNER_URL') . '', ['connect_timeout' => 0.5], \GuzzleHttp\json_encode([
                'url' => $this->scan->url,
                'dangerLevel' => '1',
                'callbackurls' => [$callbackUrl]
        ]));

        $response = $client->sendAsync($request);
        $response->wait();

    }

}
