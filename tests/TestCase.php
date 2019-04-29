<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Scan;
use App\ScanResult;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use App\HTTPClient;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Generate and Return a sample Scan with an associated ScanResult
     *
     * @return Scan
     */
    protected function generateScanWithResult(array $resultAttributes = [])
    {
        $scan = factory(Scan::class)->create();
        $scan->results()->create(factory(ScanResult::class)->make($resultAttributes)->toArray());

        return $scan;
    }

    /**
     * Generate and Return a sample Scan with an associated ScanResult where hasError: true
     *
     * @return Scan
     */
    protected function generateScanWithErrorResult()
    {
        $scan = factory(Scan::class)->create();
        $this->addErrorResult($scan);

        return $scan;
    }

    /**
     * Add an errored result to the $scan
     *
     * @return Scan
     */
    protected function addErrorResult(Scan $scan)
    {
        $scan->results()->create(factory(ScanResult::class)->make([
            'result' => json_decode(file_get_contents(base_path('tests/sampleHeaderErrorScanResult.json'))),
            'has_error' => true
        ])->toArray());

        return $scan;
    }

    /**
     * Returns a mocked HTTP-Client for testing purposes.
     *
     * @param array $mockedResponses
     * @return HTTPClient HTTP Client
     */
    protected function getMockedHttpClient(array $mockedResponses)
    {
        $mock = new MockHandler($mockedResponses);
        $handler = HandlerStack::create($mock);
        return new HTTPClient(['handler' => $handler, 'http_errors' => false]);
    }
}
