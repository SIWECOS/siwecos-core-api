<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Scan;
use App\Token;
use App\Domain;
use Illuminate\Foundation\Testing\DatabaseMigrations;


class ScanResultsTest extends TestCase
{
    use RefreshDatabase;

    public $testdomain = "http://testdomain";

    public function setUp()
    {
        parent::setUp();

        $this->token = new Token(['credits' => 50]);
        $this->token->save();

        $this->domain = new Domain(['domain' => $this->testdomain, 'token' => $this->token->token]);
        $this->domain->verified = 1;
        $this->domain->save();
    }

    /** @test */
    public function the_database_migration_works_fine() {
        Scan::create([
            'token_id' => 1,
            'url' => $this->testdomain,
            'status' => 3,  // finished Scan
            'freescan' => true
        ]);

        $this->assertEquals(1, Scan::all()->count());
        $this->assertTrue(Scan::first()->freescan);
    }

    /** @test */
    public function the_resultRawFree_method_returns_the_latest_freeScan_results() {
        // Generate some Scans with Results
        $freescan = Scan::create([
            'token_id' => 1,
            'url' => $this->testdomain,
            'status' => 3,  // finished Scan
            'freescan' => true
        ]);
        $freescan->created_at = \Carbon\Carbon::parse("1984-01-01");
        $freescan->save();

        $nonfreescan = Scan::create([
            'token_id' => 1,
            'url' => $this->testdomain,
            'status' => 3,  // finished Scan
            'freescan' => false
        ]);
        $nonfreescan->created_at = \Carbon\Carbon::parse("2000-01-01");
        $nonfreescan->save();

        // Send a GET-Request to retrieve the latest scan results
        $response = $this->get('/api/v1/domainscan?domain=' . $this->testdomain);

        // Assert a valid response and non-freescan start time
        $response->assertStatus(200);
        $this->assertEquals('1984-01-01 00:00:00.000000', $response->json()['scanStarted']['date']);
    }

    /** @test */
    public function the_resultRaw_method_returns_the_latest_nonFreeScan_results() {
        // Generate some Scans with Results
        $nonfreescan = Scan::create([
            'token_id' => 1,
            'url' => $this->testdomain,
            'status' => 3,  // finished Scan
            'freescan' => false
        ]);
        $nonfreescan->created_at = \Carbon\Carbon::parse("1984-01-01");
        $nonfreescan->save();

        $freescan = Scan::create([
            'token_id' => 1,
            'url' => $this->testdomain,
            'status' => 3,  // finished Scan
            'freescan' => true
        ]);
        $freescan->created_at = \Carbon\Carbon::parse("2018-01-01");
        $freescan->save();


        // Send a GET-Request to retrieve the latest scan results
        $response = $this->get('/api/v1/scan/result/raw?domain=' . $this->testdomain, [
            'siwecostoken' => $this->token->token
        ]);

        // Assert a valid response and non-freescan start time
        $response->assertStatus(200);
        $this->assertEquals('1984-01-01 00:00:00.000000', $response->json()['scanStarted']['date']);
    }


}
