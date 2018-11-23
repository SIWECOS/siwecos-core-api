<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use GuzzleHttp\Psr7\Response;
use App\Http\Controllers\ScanController;


const TEST_DOMAIN = "http://siwecos-test-domain";

class ScanControllerTest extends TestCase
{
    /** @test */
    public function isDomainAlive_returns_true_for_allowed_http_status_codes() {
        $allowedCases = [
            new Response(200),
            new Response(301),
            new Response(302),
            new Response(303),
            new Response(307),
            new Response(308),
        ];

        $client = $this->getMockedGuzzleClient($allowedCases);

        foreach ($allowedCases as $case) {
            $this->assertTrue(ScanController::isDomainAlive(TEST_DOMAIN, $client));
        };

        // Not allowed
        $client = $this->getMockedGuzzleClient([new Response(500)]);
        $this->assertFalse(ScanController::isDomainAlive(TEST_DOMAIN, $client));
    }
}
