<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Responses\ScanCallbackResponse;
use Carbon\Carbon;
use App\ScanResult;

class ScanCallbackResponseTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $knownDate = Carbon::create(2019, 5, 7, 11, 55, 15);
        Carbon::setTestNow($knownDate);
    }

    /** @test */
    public function the_scanCallbackResponse_has_the_defined_format()
    {
        $scan = $this->generateScanWithResult();
        $scan->update([
            'dangerLevel' => 7,
            'startedAt' => now()
        ]);

        $response = new ScanCallbackResponse($scan->refresh());

        $this->assertEquals(json_encode([
            'url' => 'https://example.org',
            'dangerLevel' => '7',
            'startedAt' => '2019-05-07T11:55:15Z',
            'finishedAt' => '2019-05-07T11:55:15Z',
            'version' => '2.0.0',
            'results' => [
                [
                    'startedAt' => now()->toIso8601ZuluString(),
                    'finishedAt' => now()->toIso8601ZuluString(),
                    "name" => "INI_S",
                    "version" => "1.0.0",
                    "hasError" => false,
                    "errorMessage" => null,
                    "score" => 100,
                    "tests" => [
                        [
                            "name" => "PHISHING",
                            "hasError" => false,
                            "errorMessage" => null,
                            "score" => 100,
                            "scoreType" => "success",
                            "testDetails" => []
                        ],
                        [
                            "name" => "SPAM",
                            "hasError" => false,
                            "errorMessage" => null,
                            "score" => 100,
                            "scoreType" => "success",
                            "testDetails" => []
                        ],
                        [
                            "name" => "MALWARE",
                            "hasError" => false,
                            "errorMessage" => null,
                            "score" => 100,
                            "scoreType" => "success",
                            "testDetails" => []
                        ]
                    ]
                ]
            ]
        ]), $response->toJson());
    }

    /** @test */
    public function if_a_scanResult_is_empty_or_has_an_error_the_message_complies_with_the_defined_format()
    {
        $scan = $this->generateScanWithResult([
            'has_error' => true,
            'result' => null,
            'scanner_code' => 'INI_S'
        ]);

        // set to failed state
        $scan->update([
            'dangerLevel' => 7,
            'startedAt' => now(),
            'finishedAt' => now()
        ]);

        $this->assertEmpty(ScanResult::first()->result);

        $response = new ScanCallbackResponse($scan->refresh());


        $this->assertEquals(json_encode([
            'url' => 'https://example.org',
            'dangerLevel' => '7',
            'startedAt' => '2019-05-07T11:55:15Z',
            'finishedAt' => '2019-05-07T11:55:15Z',
            'version' => '2.0.0',
            'withMissingScannerResults' => ['INI_S'],
            'results' => [
                // empty array
            ],
        ]), $response->toJson());
    }
}
