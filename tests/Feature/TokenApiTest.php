<?php

namespace Tests\Feature;

use App\Token;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Keygen\Keygen;
use Tests\TestCase;

const BASEURL_TOKEN = '/api/v1/token/';
const ACLLEVEL = 5;
const CREDITS = 50;

class TokenApiTest extends TestCase
{
    use DatabaseMigrations, DatabaseTransactions, WithoutMiddleware;

    protected $token;

    public function setUp()
    {
        parent::setUp();
        $this->token = new Token(['credits' => CREDITS]);
        $this->token->acl_level = ACLLEVEL;
        $this->token->save();
    }

    public function testCreateTokenTestNoCreditsGiven()
    {
        $response = $this->json('POST', BASEURL_TOKEN.'add');
        $response->assertStatus(422);
    }

    public function testCreateTokenTestWithCreditsGiven()
    {
        $response = $this->json('POST', BASEURL_TOKEN.'add', ['credits' => CREDITS, 'aclLevel' => ACLLEVEL]);
        $response->assertJsonStructure([
            'token', 'hasFailed', 'message',
        ]);
        $response->assertJson(['hasFailed' => false]);
        $response->assertStatus(200);
    }

    public function testRevokeTokenTestNoTokenGiven()
    {
        $response = $this->json('POST', BASEURL_TOKEN.'revoke');
        $response->assertStatus(422);
    }

    public function testRevokeTokenTestWrongTokenGiven()
    {
        $response = $this->json('POST', BASEURL_TOKEN.'revoke', ['token' => Keygen::alphanum(12)->generate()]);
        $response->assertStatus(404);
    }

    public function testRevokeTokenTokenGiven()
    {
        $response = $this->json('POST', BASEURL_TOKEN.'revoke', ['token' => $this->token->token]);
        $response->assertStatus(200);
    }

    public function testStatusTokenNoTokenGiven()
    {
        $response = $this->json('POST', BASEURL_TOKEN.'status');
        $response->assertStatus(422);
    }

    public function testStatusTokenWrongTokenGiven()
    {
        $response = $this->json('POST', BASEURL_TOKEN.'status', ['token' => Keygen::alphanum(24)->generate()]);
        $response->assertStatus(404);
    }

    public function testStatusToken(int $credits = CREDITS)
    {
        $response = $this->json('POST', BASEURL_TOKEN.'status', ['token' => $this->token->token]);
        $response->assertJsonStructure([
            'active', 'credits', 'active', 'hasFailed', 'message',
        ]);
        $response->assertJson(['credits' => $credits, 'aclLevel' => ACLLEVEL]);
        $response->assertJson(['hasFailed' => false]);
        $response->assertStatus(200);
    }

    public function testSetCreditsTokenNoTokenGiven()
    {
        $response = $this->json('POST', BASEURL_TOKEN.'setcredits');
        $response->assertStatus(422);
    }

    public function testSetCreditsTokenNoCreditsGiven()
    {
        $response = $this->json('POST', BASEURL_TOKEN.'setcredits', ['token' => $this->token->token]);
        $response->assertStatus(422);
    }

    public function testSetCreditsTokenCorrectData()
    {
        $response = $this->json('POST', BASEURL_TOKEN.'setcredits', ['token' => $this->token->token, 'credits' => 2 * CREDITS]);
        $response->assertStatus(200);
        $response->assertJson(['hasFailed' => false]);
        $this->testStatusToken(2 * CREDITS);
    }
}
