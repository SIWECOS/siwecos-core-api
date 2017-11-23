<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Jobs\ScanHeadersJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Scan;
use App\ScanResult;

class ScannerStartTest extends TestCase
{

    use DatabaseMigrations, DatabaseTransactions;

    /** @test */
    public function a_url_is_required()
    {
        $this->json('POST', '/api/v1/scan/start')
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'url' => []
                ]
            ]);
        }

    /** @test */
    public function the_dangerLevel_has_a_valid_range()
    {

        Queue::fake();

        $response = $this->json('POST', '/api/v1/scan/start', [
            'url' => 'https://example.com',
            'dangerLevel' => -1
        ]);

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'dangerLevel' => []
            ]
        ]);

        $response = $this->json('POST', '/api/v1/scan/start', [
            'url' => 'https://example.com',
            'dangerLevel' => 15
        ]);

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'dangerLevel' => []
            ]
        ]);

        $response = $this->json('POST', '/api/v1/scan/start', [
            'url' => 'https://example.com',
            'dangerLevel' => "five"
        ]);

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'dangerLevel' => []
            ]
        ]);

        $response = $this->json('POST', '/api/v1/scan/start', [
            'url' => 'https://example.com',
            'dangerLevel' => 4
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function the_scanner_jobs_are_dispatched()
    {
        Queue::fake();
        $this->withoutExceptionHandling();

        $response = $this->json('POST', '/api/v1/scan/start', [
            'url' => 'https://example.com'
        ]);

        $response->assertStatus(200);

        Queue::assertPushed(ScanHeadersJob::class);
    }

    /** @test */
    public function a_new_scan_is_saved_to_the_database_if_the_job_is_started()
    {
        Queue::fake();
        
        $this->assertEquals(0, Scan::all()->count());

        $response = $this->post('/api/v1/scan/start', [
            'url' => 'https://example.com'
        ]);

        $this->assertEquals(1, Scan::all()->count());
    }

    /** @test */
    public function a_scan_result_is_saved_after_the_queue_is_processed()
    {
        $this->assertEquals(0, ScanResult::all()->count());

        // TODO: Mock this test so no real query is sent

        $response = $this->post('/api/v1/scan/start', [
            'url' => 'https://example.com'
        ]);

        $this->assertEquals(1, ScanResult::all()->count());
        $this->assertEquals('https://example.com', ScanResult::first()->scan->url);
    }
    
}
