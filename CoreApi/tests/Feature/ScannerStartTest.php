<?php

namespace Tests\Feature;

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

class ScannerStartTest extends TestCase
{
    
    use DatabaseMigrations, DatabaseTransactions, WithoutMiddleware;


    protected $token;

    public function setUp()
    {
        parent::setUp();

        $this->token = Token::create([
            'credits' => 200,
            'token' => \str_random()
        ]);
    }

    
    public function a_url_is_required()
    {
        $this->json('POST', '/api/v1/scan/start', [], ['siwecosToken' => $this->token->token])
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'url' => []
                ]
            ]);
        }

    
    public function the_dangerLevel_has_a_valid_range()
    {

        Queue::fake();

        $response = $this->json('POST', '/api/v1/scan/start', [
            'url' => 'https://example.com',
            'dangerLevel' => -1
        ], ['siwecosToken' => $this->token->token]);

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'dangerLevel' => []
            ]
        ]);

        $response = $this->json('POST', '/api/v1/scan/start', [
            'url' => 'https://example.com',
            'dangerLevel' => 15
        ], ['siwecosToken' => $this->token->token]);

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'dangerLevel' => []
            ]
        ]);

        $response = $this->json('POST', '/api/v1/scan/start', [
            'url' => 'https://example.com',
            'dangerLevel' => "five"
        ], ['siwecosToken' => $this->token->token]);

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'dangerLevel' => []
            ]
        ]);

        $response = $this->json('POST', '/api/v1/scan/start', [
            'url' => 'https://example.com',
            'dangerLevel' => 4
        ], ['siwecosToken' => $this->token->token]);

        $response->assertStatus(200);
    }

    
    public function the_scanner_jobs_are_dispatched()
    {
        Queue::fake();
        $this->withoutExceptionHandling();

        $response = $this->json('POST', '/api/v1/scan/start', [
            'url' => 'https://example.com'
        ], ['siwecosToken' => $this->token->token]);

        $response->assertStatus(200);

        Queue::assertPushed(ScanHeadersJob::class);
    }

    
    public function a_new_scan_is_saved_to_the_database_if_the_job_is_started()
    {
        Queue::fake();
        
        $this->assertEquals(0, Scan::all()->count());

        $response = $this->post('/api/v1/scan/start', [
            'url' => 'https://example.com'
        ], ['siwecosToken' => $this->token->token]);

        $this->assertEquals(1, Scan::all()->count());
    }

    
    public function a_scan_result_is_saved_after_the_queue_is_processed()
    {
        $this->assertEquals(0, ScanResult::all()->count());

        // TODO: Mock this test so no real query is sent

        $response = $this->post('/api/v1/scan/start', [
            'url' => 'https://example.com'
        ], ['siwecosToken' => $this->token->token]);

        $this->assertEquals(1, ScanResult::all()->count());
        $this->assertEquals('https://example.com', ScanResult::first()->scan->url);
    }
    
}
