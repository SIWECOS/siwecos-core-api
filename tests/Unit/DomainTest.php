<?php

namespace Tests\Unit;

use App\Domain;
use Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Exception\RequestException;

class DomainTest extends TestCase
{

    /** @test */
    public function getDomainURL_returns_https_if_no_protocol_is_given_and_https_is_available()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);

        $domain = 'example.com';
        $this->assertEquals('https://example.com', Domain::getDomainURL($domain, $client));
    }

    /** @test */
    public function getDomainURL_returns_https_if_protocol_is_given_and_https_is_available()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);

        $domain = 'https://example.com';
        $this->assertEquals('https://example.com', Domain::getDomainURL($domain, $client));
    }

    /** @test */
    public function getDomainURL_returns_http_if_protocol_is_given_but_https_is_unavailable()
    {
        $client = $this->getMockedGuzzleClient([
            // Response for given domain/url
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for first test https://
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for test http://
            new Response(200)
        ]);

        $domain = 'https://example.com';
        $this->assertEquals('http://example.com', Domain::getDomainURL($domain, $client));
    }

    /** @test */
    public function getDomainURL_returns_http_if_no_protocol_is_given_but_https_is_unavailable()
    {
        $client = $this->getMockedGuzzleClient([
            // Response for first test https://
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for test http://
            new Response(200)
        ]);

        $domain = 'example.com';
        $this->assertEquals('http://example.com', Domain::getDomainURL($domain, $client));
    }

    /** @test */
    public function getDomainURL_returns_alternativeAvailable_if_protocol_is_given_but_https_is_unavailable_but_httpsWww_is_available()
    {
        $client = $this->getMockedGuzzleClient([
            // Response for given domain/url
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for first test https://
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for test http://
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for https://www.
            new Response(200)
        ]);

        $domain = 'https://example.com';

        $result = Domain::getDomainURL($domain, $client);
        $this->assertArrayHasKey('notAvailable', $result);
        $this->assertEquals('www.example.com', $result['alternativeAvailable']);
    }

    /** @test */
    public function getDomainURL_returns_alternativeAvailable_if_protocol_is_given_but_https_is_unavailable_and_only_httpWww_is_available()
    {
        $client = $this->getMockedGuzzleClient([
            // Response for given domain/url
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for first test https://
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for test http://
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for https://www.
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for http://www.
            new Response(200)
        ]);

        $domain = 'https://example.com';

        $result = Domain::getDomainURL($domain, $client);
        $this->assertArrayHasKey('notAvailable', $result);
        $this->assertEquals('www.example.com', $result['alternativeAvailable']);
    }

    /** @test */
    public function getDomainURL_returns_alternativeAvailable_if_no_protocol_is_given_but_https_is_unavailable_and_only_httpWww_is_available()
    {
        $client = $this->getMockedGuzzleClient([
            // Response for first test https://
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for test http://
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for https://www.
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for http://www.
            new Response(200)
        ]);

        $domain = 'example.com';

        $result = Domain::getDomainURL($domain, $client);

        $this->assertArrayHasKey('notAvailable', $result);
        $this->assertEquals('www.example.com', $result['alternativeAvailable']);
    }

    /** @test */
    public function getDomainURL_returns_httpWww_if_protocol_is_given_and_httpWww_is_available()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200)
        ]);

        $domain = 'http://www.example.com';
        $this->assertEquals('http://www.example.com', Domain::getDomainURL($domain, $client));
    }

    /** @test */
    public function getDomainURL_returns_httpWww_if_no_protocol_and_www_is_given_but_https_is_unavailable_and_only_httpWww_is_available()
    {
        $client = $this->getMockedGuzzleClient([
            // Response for first test https://www.
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for test http://www.
            new Response(200)
        ]);

        $domain = 'www.example.com';
        $this->assertEquals('http://www.example.com', Domain::getDomainURL($domain, $client));
    }

    /** @test */
    public function getDomainURL_returns_alternativeAvailable_if_httpsWww_is_given_but_www_is_unavailable_and_https_is_available()
    {
        $client = $this->getMockedGuzzleClient([
            // Response for first test https://www.
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for first test http://www.
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for test https://
            new Response(200)
        ]);

        $domain = 'www.example.com';

        $result = Domain::getDomainURL($domain, $client);
        $this->assertArrayHasKey('notAvailable', $result);
        $this->assertEquals('example.com', $result['alternativeAvailable']);
    }

    /** @test */
    public function getDomainURL_returns_alternativeAvailable_if_httpsWww_is_given_but_www_is_unavailable_and_only_http_is_available()
    {
        $client = $this->getMockedGuzzleClient([
            // Response for first test https://www.
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for first test http://www.
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for test https://
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for test http://
            new Response(200)
        ]);

        $domain = 'www.example.com';

        $result = Domain::getDomainURL($domain, $client);
        $this->assertArrayHasKey('notAvailable', $result);
        $this->assertEquals('example.com', $result['alternativeAvailable']);
    }

    /** @test */
    public function getDomainURL_returns_null_if_no_valid_domain_is_entered()
    {
        $client = $this->getMockedGuzzleClient([
            // Response for first test https://www.
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for first test http://www.
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for test https://
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
            // Response for test http://
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
        ]);

        $domain = 'lorem';
        $this->assertNull(Domain::getDomainURL($domain, $client));
    }


    /**
     * This method sets and activates the GuzzleHttp Mocking functionality.
     * @param array $responses
     * @return Client
     */
    protected function getMockedGuzzleClient(array $responses)
    {
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        return (new Client(["handler" => $handler])) ;
    }
}
