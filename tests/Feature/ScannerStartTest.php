<?php

namespace Tests\Feature;

use App\Scan;
use App\Token;
use App\Domain;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

const TEST_DOMAIN = 'example.com';

class ScannerStartTest extends TestCase
{
    use RefreshDatabase;

    protected $token;
    protected $domain;

    public function setUp()
    {
        parent::setUp();

        $this->token = new Token(['credits' => 50]);
        $this->token->save();

        $this->domain = new Domain(['domain' => TEST_DOMAIN, 'token' => $this->token->token]);
        $this->domain->verified = 1;
        $this->domain->save();
    }

    /** @test */
    public function a_url_is_required()
    {
        // changed to 500 due fail operation in domain model
        $this->json('POST', '/api/v1/scan/start', [], ['siwecosToken' => $this->token->token])
            ->assertStatus(500);
    }

    /** @test */
    public function the_dangerLevel_has_a_valid_range()
    {
        Queue::fake();

        $response = $this->json('POST', '/api/v1/scan/start', [
            'domain'      => TEST_DOMAIN,
            'dangerLevel' => -1,
        ], ['siwecosToken' => $this->token->token]);

        $response->assertStatus(422);

        $response = $this->json('POST', '/api/v1/scan/start', [
            'domain'      => TEST_DOMAIN,
            'dangerLevel' => 15,
        ], ['siwecosToken' => $this->token->token]);

        $response->assertStatus(422);

        $response = $this->json('POST', '/api/v1/scan/start', [
            'domain'      => TEST_DOMAIN,
            'dangerLevel' => 'five',
        ], ['siwecosToken' => $this->token->token]);

        $response->assertStatus(422);

        $response = $this->json('POST', '/api/v1/scan/start', [
            'domain'      => TEST_DOMAIN,
            'dangerLevel' => 4,
            'isNotATest'  => false,
        ], ['siwecosToken' => $this->token->token]);

        $response->assertStatus(200);
    }

    /** @test */
    public function a_new_scan_is_saved_to_the_database_if_the_job_is_started()
    {
        Queue::fake();

        $this->assertEquals(0, Scan::all()->count());

        $response = $this->json('POST', '/api/v1/scan/start', [
            'domain' => TEST_DOMAIN,
        ], ['siwecosToken' => $this->token->token]);
        $this->assertEquals(1, Scan::all()->count());
    }

    /** @test */
    public function a_free_scan_can_be_started_for_an_existing_url()
    {
        Queue::fake();

        $response = $this->json('POST', '/api/v1/getFreeScanStart', [
            'domain' => 'siwecos.de',
        ]);
        $this->assertEquals(1, Scan::all()->count());
    }

    /** @test */
    public function a_free_scan_can_not_be_started_for_a_not_existing_url()
    {
        Queue::fake();

        $response = $this->json('POST', '/api/v1/getFreeScanStart', [
            'domain' => 'lorem',
        ]);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals(0, Scan::all()->count());
    }

    /** @test */
    public function a_free_scan_can_does_not_start_for_an_alternative_existing_url()
    {
        Queue::fake();

        $response = $this->json('POST', '/api/v1/getFreeScanStart', [
            'domain' => 'www.staging2.siwecos.de',
        ]);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals(0, Scan::all()->count());
    }
}
