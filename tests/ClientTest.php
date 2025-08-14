<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component Tests
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Tests\Classes\Http;

use PHPUnit\Framework\TestCase;
use FloatPHP\Classes\Http\Client;

/**
 * Client class tests.
 */
final class ClientTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = new Client('https://httpbin.org');
    }

    /**
     * Test client initialization.
     */
    public function testClientInitialization(): void
    {
        $client = new Client('https://example.com');
        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * Test client initialization with params.
     */
    public function testClientInitializationWithParams(): void
    {
        $params = ['timeout' => 30, 'redirect' => 5];
        $client = new Client('https://example.com', $params);
        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * Test HTTP methods constants.
     */
    public function testHttpMethodConstants(): void
    {
        $this->assertEquals('GET', Client::GET);
        $this->assertEquals('POST', Client::POST);
        $this->assertEquals('HEAD', Client::HEAD);
        $this->assertEquals('PUT', Client::PUT);
        $this->assertEquals('PATCH', Client::PATCH);
        $this->assertEquals('OPTIONS', Client::OPTIONS);
        $this->assertEquals('DELETE', Client::DELETE);
    }

    /**
     * Test default timeout constant.
     */
    public function testTimeoutConstant(): void
    {
        $this->assertEquals(10, Client::TIMEOUT);
    }

    /**
     * Test default redirect constant.
     */
    public function testRedirectConstant(): void
    {
        $this->assertEquals(3, Client::REDIRECT);
    }

    /**
     * Test user agent constant.
     */
    public function testUserAgentConstant(): void
    {
        $this->assertIsArray(Client::USERAGENT);
        $this->assertNotEmpty(Client::USERAGENT);
    }

    /**
     * Test GET request method.
     */
    public function testGetRequest(): void
    {
        $response = $this->client->get('/get');
        $this->assertInstanceOf(Client::class, $response);
    }

    /**
     * Test POST request method.
     */
    public function testPostRequest(): void
    {
        $response = $this->client->post('/post', ['key' => 'value']);
        $this->assertInstanceOf(Client::class, $response);
    }

    /**
     * Test HEAD request method.
     */
    public function testHeadRequest(): void
    {
        $response = $this->client->head('/get');
        $this->assertInstanceOf(Client::class, $response);
    }

    /**
     * Test PUT request method.
     */
    public function testPutRequest(): void
    {
        $response = $this->client->put('/put', ['key' => 'value']);
        $this->assertInstanceOf(Client::class, $response);
    }

    /**
     * Test PATCH request method.
     */
    public function testPatchRequest(): void
    {
        $response = $this->client->patch('/patch', ['key' => 'value']);
        $this->assertInstanceOf(Client::class, $response);
    }

    /**
     * Test OPTIONS request method.
     */
    public function testOptionsRequest(): void
    {
        $response = $this->client->options('/get');
        $this->assertInstanceOf(Client::class, $response);
    }

    /**
     * Test DELETE request method.
     */
    public function testDeleteRequest(): void
    {
        $response = $this->client->delete('/delete');
        $this->assertInstanceOf(Client::class, $response);
    }

    /**
     * Test method chaining.
     */
    public function testMethodChaining(): void
    {
        $response = $this->client
            ->setTimeout(15)
            ->setUserAgent('Custom-Agent')
            ->follow()
            ->return()
            ->get('/get');
        
        $this->assertInstanceOf(Client::class, $response);
    }

    /**
     * Test set timeout.
     */
    public function testSetTimeout(): void
    {
        $result = $this->client->setTimeout(30);
        $this->assertInstanceOf(Client::class, $result);
    }

    /**
     * Test set redirect.
     */
    public function testSetRedirect(): void
    {
        $result = $this->client->setRedirect(5);
        $this->assertInstanceOf(Client::class, $result);
    }

    /**
     * Test set encoding.
     */
    public function testSetEncoding(): void
    {
        $result = $this->client->setEncoding('gzip');
        $this->assertInstanceOf(Client::class, $result);
    }

    /**
     * Test set user agent.
     */
    public function testSetUserAgent(): void
    {
        $result = $this->client->setUserAgent('Test-Agent');
        $this->assertInstanceOf(Client::class, $result);
    }

    /**
     * Test return method.
     */
    public function testReturn(): void
    {
        $result = $this->client->return();
        $this->assertInstanceOf(Client::class, $result);
    }

    /**
     * Test follow method.
     */
    public function testFollow(): void
    {
        $result = $this->client->follow();
        $this->assertInstanceOf(Client::class, $result);
    }

    /**
     * Test header in method.
     */
    public function testHeaderIn(): void
    {
        $result = $this->client->headerIn();
        $this->assertInstanceOf(Client::class, $result);
    }

    /**
     * Test get response.
     */
    public function testGetResponse(): void
    {
        $this->client->get('/get');
        $response = $this->client->getResponse();
        $this->assertIsArray($response);
    }

    /**
     * Test is gateway detection.
     */
    public function testIsGateway(): void
    {
        $client = new Client();
        $this->assertTrue($client->isCurl() || $client->isStream());
    }

    /**
     * Test gateway availability.
     */
    public function testHasGateway(): void
    {
        $this->assertTrue(Client::hasCurl() || Client::hasStream());
    }

    /**
     * Test get user agent.
     */
    public function testGetUserAgent(): void
    {
        $ua = Client::getUserAgent();
        $this->assertIsString($ua);
        $this->assertNotEmpty($ua);
    }

    /**
     * Test get params.
     */
    public function testGetParams(): void
    {
        $params = Client::getParams();
        $this->assertIsArray($params);
        $this->assertArrayHasKey('timeout', $params);
        $this->assertArrayHasKey('redirect', $params);
    }

    /**
     * Test format header.
     */
    public function testFormatHeader(): void
    {
        $header = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
        $formatted = Client::formatHeader($header);
        $this->assertIsString($formatted);
        $this->assertStringContainsString('Content-Type: application/json', $formatted);
    }

    /**
     * Test pattern getters and setters.
     */
    public function testPatternMethods(): void
    {
        Client::setPattern(['custom' => '/test/']);
        $pattern = Client::getPattern('status');
        $this->assertIsString($pattern);
    }
}
