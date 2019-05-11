<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Responses\ScanCallbackResponse;
use Carbon\Carbon;

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
            'started_at' => now()
        ]);

        $response = new ScanCallbackResponse($scan->refresh());

        $this->assertJsonStringEqualsJsonString(json_encode([
            'url' => 'https://example.org',
            'dangerLevel' => '7',
            'started_at' => '2019-05-07 11:55:15',
            'finished_at' => '2019-05-07 11:55:15',
            'version' => '2.0.0',
            'results' => [
                [
                    'started_at' => now()->toDateTimeString(),
                    'finished_at' => now()->toDateTimeString(),
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
}
