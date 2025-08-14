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
use FloatPHP\Classes\Http\Stream;

/**
 * Stream class tests.
 */
final class StreamTest extends TestCase
{
    /**
     * Test stream constants.
     */
    public function testStreamConstants(): void
    {
        $this->assertEquals('http', Stream::HTTP);
        $this->assertEquals('ftp', Stream::FTP);
        $this->assertEquals('ssh2', Stream::SSH);
        $this->assertEquals('phar', Stream::PHAR);
        $this->assertEquals('php', Stream::PHP);
        $this->assertEquals('file', Stream::FILE);
        $this->assertEquals('glob', Stream::GLOB);
        $this->assertEquals('data', Stream::DATA);
        $this->assertEquals('tcp', Stream::TCP);
        $this->assertEquals('udp', Stream::UDP);
        $this->assertEquals('ssl', Stream::SSL);
        $this->assertEquals('tls', Stream::TLS);
    }

    /**
     * Test set transport.
     */
    public function testSetTransport(): void
    {
        Stream::setTransport("https");
        $this->assertTrue(true); // Method should execute without error
        
        Stream::setTransport(Stream::FTP);
        $this->assertTrue(true);
    }

    /**
     * Test create stream context.
     */
    public function testCreateStreamContext(): void
    {
        $context = Stream::create();
        $this->assertIsResource($context);
    }

    /**
     * Test create stream context with options.
     */
    public function testCreateStreamContextWithOptions(): void
    {
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => 'Content-Type: application/json',
                'timeout' => 30
            ]
        ];
        
        $context = Stream::create($options);
        $this->assertIsResource($context);
    }

    /**
     * Test create stream context with params.
     */
    public function testCreateStreamContextWithParams(): void
    {
        $options = [];
        $params = ['notification' => 'stream_notification_callback'];
        
        $context = Stream::create($options, $params);
        $this->assertIsResource($context);
    }

    /**
     * Test stream request method exists.
     */
    public function testStreamRequestMethodExists(): void
    {
        $this->assertTrue(method_exists(Stream::class, 'request'));
    }

    /**
     * Test stream request basic functionality.
     */
    public function testStreamRequestBasicFunctionality(): void
    {
        if (!ini_get('allow_url_fopen')) {
            $this->markTestSkipped('allow_url_fopen is disabled');
        }

        $response = Stream::request('https://httpbin.org/get');
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('header', $response);
        $this->assertArrayHasKey('body', $response);
        $this->assertArrayHasKey('error', $response);
    }

    /**
     * Test stream request with parameters.
     */
    public function testStreamRequestWithParameters(): void
    {
        if (!ini_get('allow_url_fopen')) {
            $this->markTestSkipped('allow_url_fopen is disabled');
        }

        $params = [
            'method' => 'GET',
            'timeout' => 10,
            'header' => ['Accept' => 'application/json']
        ];
        
        $response = Stream::request('https://httpbin.org/get', $params);
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
    }

    /**
     * Test HTTP stream wrapper.
     */
    public function testHttpStreamWrapper(): void
    {
        $this->assertTrue(in_array('http', stream_get_wrappers()));
        $this->assertTrue(in_array('https', stream_get_wrappers()));
    }

    /**
     * Test file stream wrapper.
     */
    public function testFileStreamWrapper(): void
    {
        $this->assertTrue(in_array('file', stream_get_wrappers()));
    }

    /**
     * Test data stream wrapper.
     */
    public function testDataStreamWrapper(): void
    {
        $this->assertTrue(in_array('data', stream_get_wrappers()));
    }

    /**
     * Test PHP stream wrapper.
     */
    public function testPhpStreamWrapper(): void
    {
        $this->assertTrue(in_array('php', stream_get_wrappers()));
    }

    /**
     * Test stream socket functionality.
     */
    public function testStreamSocketFunctionality(): void
    {
        $this->assertTrue(function_exists('stream_socket_client'));
        $this->assertTrue(function_exists('stream_socket_server'));
        $this->assertTrue(function_exists('stream_context_create'));
    }

    /**
     * Test stream methods exist.
     */
    public function testStreamMethodsExist(): void
    {
        $methods = [
            'setTransport',
            'createContext',
            'request',
            'getResponseStatus',
            'getResponseHeader',
            'getResponseBody',
            'reset'
        ];
        
        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(Stream::class, $method),
                "Method {$method} should exist in Stream class"
            );
        }
    }

    /**
     * Test stream transport protocols.
     */
    public function testStreamTransportProtocols(): void
    {
        $transports = stream_get_transports();
        
        $this->assertContains('tcp', $transports);
        $this->assertContains('udp', $transports);
        
        if (extension_loaded('openssl')) {
            $this->assertContains('ssl', $transports);
            $this->assertContains('tls', $transports);
        }
    }

    /**
     * Test get response status method.
     */
    public function testGetResponseStatus(): void
    {
        $status = Stream::getResponseStatus();
        $this->assertIsArray($status);
    }

    /**
     * Test get response header method.
     */
    public function testGetResponseHeader(): void
    {
        $header = Stream::getResponseHeader();
        $this->assertIsArray($header);
    }

    /**
     * Test get response body method.
     */
    public function testGetResponseBody(): void
    {
        $body = Stream::getResponseBody();
        $this->assertIsString($body);
    }

    /**
     * Test reset method.
     */
    public function testReset(): void
    {
        Stream::reset();
        $this->assertTrue(true); // Should execute without error
    }

    /**
     * Test stream with SSL context.
     */
    public function testStreamWithSslContext(): void
    {
        $options = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];
        
        $context = Stream::create($options);
        $this->assertIsResource($context);
    }

    /**
     * Test stream with HTTP headers.
     */
    public function testStreamWithHttpHeaders(): void
    {
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: FloatPHP/1.5.x',
                    'Accept: application/json',
                    'Content-Type: application/json'
                ]
            ]
        ];
        
        $context = Stream::create($options);
        $this->assertIsResource($context);
    }

    /**
     * Test stream with POST data.
     */
    public function testStreamWithPostData(): void
    {
        $postData = json_encode(['test' => 'data']);
        
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $postData
            ]
        ];
        
        $context = Stream::create($options);
        $this->assertIsResource($context);
    }

    /**
     * Test stream timeout configuration.
     */
    public function testStreamTimeoutConfiguration(): void
    {
        $options = [
            'http' => [
                'timeout' => 30
            ]
        ];
        
        $context = Stream::create($options);
        $this->assertIsResource($context);
    }

    /**
     * Test stream with follow redirects.
     */
    public function testStreamWithFollowRedirects(): void
    {
        $options = [
            'http' => [
                'follow_location' => 1,
                'max_redirects' => 3
            ]
        ];
        
        $context = Stream::create($options);
        $this->assertIsResource($context);
    }

    /**
     * Test stream error handling.
     */
    public function testStreamErrorHandling(): void
    {
        if (!ini_get('allow_url_fopen')) {
            $this->markTestSkipped('allow_url_fopen is disabled');
        }

        // Test with invalid URL
        $response = Stream::request('http://invalid-domain-12345.com');
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertTrue($response['error']);
    }
}
