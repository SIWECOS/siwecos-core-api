<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\ScanResult;

class ScannerCallbackTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_scannerResponse_can_be_received()
    {
        $scanResult = $this->generateScanWithResult([
            'result' => null
        ])->results->first();

        $scannerResponseJson = json_decode(file_get_contents(base_path('tests/sampleScanResult.json')));
        $response = $this->json('POST', '/api/v2/callback/' . $scanResult->id, collect($scannerResponseJson)->toArray());

        $response->assertStatus(200);
        $this->assertEquals($scannerResponseJson, json_decode(ScanResult::first()->result));
    }

    /** @test */
    public function if_the_scanner_reports_an_error_it_will_be_reflected_in_the_scanResult()
    {
        $scanResult = $this->generateScanWithResult([
            'result' => null
        ])->results->first();

        $scannerResponseJson = json_decode(file_get_contents(base_path('tests/sampleHeaderErrorScanResult.json')));
        $response = $this->json('POST', '/api/v2/callback/' . $scanResult->id, collect($scannerResponseJson)->toArray());

        $response->assertStatus(200);
        $this->assertEquals($scannerResponseJson, json_decode(ScanResult::first()->result));
        $this->assertTrue(ScanResult::first()->result->get('hasError'));
    }
}
