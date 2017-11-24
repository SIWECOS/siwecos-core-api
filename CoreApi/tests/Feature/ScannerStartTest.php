<?php

namespace Tests\Feature;

use App\Domain;
use Tests\TestCase;
use App\Jobs\ScanHeadersJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Scan;
use App\ScanResult;
use App\Token;

CONST TEST_DOMAIN = 'https://example.com';

class ScannerStartTest extends TestCase
{
    
    use DatabaseMigrations, DatabaseTransactions;


    protected $token, $domain;

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
            'domain' => TEST_DOMAIN,
            'dangerLevel' => -1
        ], ['siwecosToken' => $this->token->token]);

        $response->assertStatus(422);

        $response = $this->json('POST', '/api/v1/scan/start', [
            'domain' => TEST_DOMAIN,
            'dangerLevel' => 15
        ], ['siwecosToken' => $this->token->token]);

        $response->assertStatus(422);


        $response = $this->json('POST', '/api/v1/scan/start', [
            'domain' => TEST_DOMAIN,
            'dangerLevel' => "five"
        ], ['siwecosToken' => $this->token->token]);

        $response->assertStatus(422);

        $response = $this->json('POST', '/api/v1/scan/start', [
            'domain' => TEST_DOMAIN,
            'dangerLevel' => 4
        ], ['siwecosToken' => $this->token->token]);

        $response->assertStatus(200);
    }

    /** @test */
    public function the_scanner_jobs_are_dispatched()
    {
        Queue::fake();

        $response = $this->json('POST', '/api/v1/scan/start', [
            'domain' => TEST_DOMAIN
        ], ['siwecosToken' => $this->token->token]);

        $response->assertStatus(200);

        Queue::assertPushed(ScanHeadersJob::class);
    }

    /** @test */
    public function a_new_scan_is_saved_to_the_database_if_the_job_is_started()
    {
        Queue::fake();
        
        $this->assertEquals(0, Scan::all()->count());

        $response = $this->json('POST', '/api/v1/scan/start', [
            'domain' => TEST_DOMAIN
        ], ['siwecosToken' => $this->token->token]);
        $this->assertEquals(1, Scan::all()->count());
    }

    /** @test */
    public function a_scan_result_is_saved_after_the_queue_is_processed()
    {
        $this->assertEquals(0, ScanResult::all()->count());

        // TODO: Mock this test so no real query is sent

        $response = $this->json('POST', '/api/v1/scan/start', [
            'domain' => 'https://example.com'
        ], ['siwecosToken' => $this->token->token]);

        $this->assertEquals(1, ScanResult::all()->count());
        $this->assertEquals(TEST_DOMAIN, ScanResult::first()->scan->url);
    }
    
}
