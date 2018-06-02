<?php

namespace Tests\Feature;

use App\Domain;
use App\Token;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

const BASEURL_DOMAIN = '/api/v1/domain/';
const CREDITS = 50;
const TEST_DOMAIN1 = 'https://example.com';
const TEST_DOMAIN2 = 'https://ex.com';
const TEST_DOMAIN3 = 'https://examp.com';

class DomainApiTest extends TestCase
{
    use DatabaseMigrations, DatabaseTransactions;

    protected $token;
    protected $domain;
    protected $tokenHeaderArray;

    public function testListDomainNoToken()
    {
        $response = $this->json('GET', '/api/v1/domains');
        $response->assertStatus(403);
    }

    public function testListDomains()
    {
        $response = $this->json('GET', '/api/v1/domains', [], $this->tokenHeaderArray);
        $response->assertJsonStructure(['domains', 'hasFailed', 'message']);
        $response->assertStatus(200);
    }

    public function testAddDomainNoToken()
    {
        $respone = $this->json('POST', BASEURL_DOMAIN.'add', ['domain' => TEST_DOMAIN2]);
        $respone->assertStatus(403);
    }

    public function testAddDomainInvalidDomain()
    {
        $response = $this->json('POST', BASEURL_DOMAIN.'add', ['domain' => 'loremipsum'], $this->tokenHeaderArray);
        $response->assertStatus(422);
    }

    public function testAddDomainNoDomain()
    {
        $response = $this->json('POST', BASEURL_DOMAIN.'add', [], $this->tokenHeaderArray);
        $response->assertStatus(422);
    }

    public function testAddDomainValidData()
    {
        $response = $this->json('POST', BASEURL_DOMAIN.'add', ['domain' => TEST_DOMAIN2], $this->tokenHeaderArray);
        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'domainToken', 'verificationStatus', 'domainId']);
        $response->assertJson(['hasFailed' => false]);
    }

    public function testRemoveDomainNoToken()
    {
        $response = $this->json('POST', BASEURL_DOMAIN.'remove', ['domain' => TEST_DOMAIN1]);
        $response->assertStatus(403);
    }

    public function testRemoveDomainNoDomainFound()
    {
        $response = $this->json('POST', BASEURL_DOMAIN.'remove', ['domain' => TEST_DOMAIN2], $this->tokenHeaderArray);
        $response->assertStatus(500);
    }

    public function testRemoveDomainNoAccessDomain()
    {
        $testToken = new Token(['credits' => 50]);
        $testToken->save();
        $testDomain = new Domain(['token' => $testToken->token, 'domain' => TEST_DOMAIN3]);
        $testDomain->save();
        $response = $this->json('POST', BASEURL_DOMAIN.'remove', ['domain' => TEST_DOMAIN3], $this->tokenHeaderArray);
        $response->assertStatus(500);
    }

    public function testRemoveDomainNoDomain()
    {
        $response = $this->json('POST', BASEURL_DOMAIN.'remove', [], $this->tokenHeaderArray);
        $response->assertStatus(422);
    }

    public function testRemoveDomainValidData()
    {
        $response = $this->json('POST', BASEURL_DOMAIN.'remove', ['domain' => TEST_DOMAIN1], $this->tokenHeaderArray);
        $response->assertStatus(200);
        $response->assertJsonStructure(['hasFailed', 'message']);
        $response->assertJson(['hasFailed' => false]);
    }

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        // setup token
        $this->token = new Token(['credits' => CREDITS]);
        $this->token->save();
        $this->tokenHeaderArray = ['siwecosToken' => $this->token->token];

        // setup domain
        $this->domain = new Domain(['token' => $this->token->token, 'domain' => TEST_DOMAIN1]);
        $this->domain->save();
    }
}
