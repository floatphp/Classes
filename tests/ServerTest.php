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
use FloatPHP\Classes\Http\Server;

/**
 * Server class tests.
 */
final class ServerTest extends TestCase
{
    protected function setUp(): void
    {
        // Set up some sample SERVER data
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/test/path';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '443';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Test Browser';
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        $_SERVER['PHP_AUTH_USER'] = 'testuser';
        $_SERVER['PHP_AUTH_PW'] = 'testpass';
    }

    /**
     * Test get server value.
     */
    public function testGetValue(): void
    {
        $host = Server::get('HTTP_HOST');
        $this->assertEquals('example.com', $host);
    }

    /**
     * Test get server value with formatting.
     */
    public function testGetValueWithFormatting(): void
    {
        $host = Server::get('http-host', true);
        $this->assertEquals('example.com', $host);
    }

    /**
     * Test get server value without formatting.
     */
    public function testGetValueWithoutFormatting(): void
    {
        $host = Server::get('http-host', false);
        $this->assertNull($host); // Should be null since exact key doesn't exist
        
        $host = Server::get('HTTP_HOST', false);
        $this->assertEquals('example.com', $host);
    }

    /**
     * Test get all server values.
     */
    public function testGetAllValues(): void
    {
        $server = Server::get();
        $this->assertIsArray($server);
        $this->assertArrayHasKey('HTTP_HOST', $server);
    }

    /**
     * Test get non-existent server value.
     */
    public function testGetNonExistentValue(): void
    {
        $value = Server::get('NON_EXISTENT_KEY');
        $this->assertNull($value);
    }

    /**
     * Test check if server value is set.
     */
    public function testIsSetted(): void
    {
        $this->assertTrue(Server::isSet('HTTP_HOST'));
        $this->assertFalse(Server::isSet('NON_EXISTENT'));
    }

    /**
     * Test check if any server values are set.
     */
    public function testIsSettedAny(): void
    {
        $this->assertTrue(Server::isSet()); // $_SERVER should always have values
    }

    /**
     * Test get request method.
     */
    public function testGetMethod(): void
    {
        $method = Server::getMethod();
        $this->assertEquals('GET', $method);
    }

    /**
     * Test get host.
     */
    public function testGetHost(): void
    {
        $host = Server::getHost();
        $this->assertEquals('example.com', $host);
    }

    /**
     * Test get URL.
     */
    public function testGetUrl(): void
    {
        $url = Server::getUrl();
        $this->assertIsString($url);
        $this->assertStringContains('example.com', $url);
    }

    /**
     * Test check basic authentication.
     */
    public function testIsBasicAuth(): void
    {
        $this->assertTrue(Server::isBasicAuth());
    }

    /**
     * Test get basic auth user.
     */
    public function testGetBasicAuthUser(): void
    {
        $user = Server::getBasicAuthUser();
        $this->assertEquals('testuser', $user);
    }

    /**
     * Test get basic auth password.
     */
    public function testGetBasicAuthPwd(): void
    {
        $password = Server::getBasicAuthPwd();
        $this->assertEquals('testpass', $password);
    }

    /**
     * Test get user agent.
     */
    public function testGetUserAgent(): void
    {
        $ua = Server::getUserAgent();
        $this->assertEquals('Mozilla/5.0 Test Browser', $ua);
    }

    /**
     * Test get IP address.
     */
    public function testGetIp(): void
    {
        $ip = Server::getIp();
        $this->assertEquals('192.168.1.100', $ip);
    }

    /**
     * Test check if HTTPS.
     */
    public function testIsSsl(): void
    {
        $this->assertTrue(Server::isSsl());
    }

    /**
     * Test check if AJAX.
     */
    public function testIsAjax(): void
    {
        // Should be false initially
        $this->assertFalse(Server::isAjax());
        
        // Set AJAX header
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertTrue(Server::isAjax());
    }

    /**
     * Test get protocol.
     */
    public function testGetProtocol(): void
    {
        $protocol = Server::getProtocol();
        $this->assertEquals('https', $protocol);
    }

    /**
     * Test get port.
     */
    public function testGetPort(): void
    {
        $port = Server::getPort();
        $this->assertEquals('443', $port);
    }

    /**
     * Test without HTTPS.
     */
    public function testWithoutHttps(): void
    {
        unset($_SERVER['HTTPS']);
        $_SERVER['SERVER_PORT'] = '80';
        
        $this->assertFalse(Server::isSsl());
        $protocol = Server::getProtocol();
        $this->assertEquals('http', $protocol);
    }

    /**
     * Test with different HTTPS values.
     */
    public function testWithDifferentHttpsValues(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $this->assertFalse(Server::isSsl());
        
        $_SERVER['HTTPS'] = '1';
        $this->assertTrue(Server::isSsl());
        
        $_SERVER['HTTPS'] = 'true';
        $this->assertTrue(Server::isSsl());
    }

    /**
     * Test get request URI.
     */
    public function testGetRequestUri(): void
    {
        $uri = Server::get('REQUEST_URI');
        $this->assertEquals('/test/path', $uri);
    }

    /**
     * Test server with proxy headers.
     */
    public function testWithProxyHeaders(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.1, 192.168.1.100';
        $_SERVER['HTTP_X_REAL_IP'] = '203.0.113.1';
        
        // Test IP detection with proxy
        $ip = Server::getIp();
        $this->assertIsString($ip);
    }

    /**
     * Test server with custom headers.
     */
    public function testWithCustomHeaders(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer token123';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        
        $auth = Server::get('HTTP_AUTHORIZATION');
        $this->assertEquals('Bearer token123', $auth);
        
        $accept = Server::get('HTTP_ACCEPT');
        $this->assertEquals('application/json', $accept);
    }

    /**
     * Test authorization headers method.
     */
    public function testGetAuthorizationHeaders(): void
    {
        $this->assertTrue(method_exists(Server::class, 'getAuthorizationHeaders'));
    }

    /**
     * Test without basic auth.
     */
    public function testWithoutBasicAuth(): void
    {
        unset($_SERVER['PHP_AUTH_USER']);
        unset($_SERVER['PHP_AUTH_PW']);
        
        $this->assertFalse(Server::isBasicAuth());
        $this->assertEquals('', Server::getBasicAuthUser());
        $this->assertEquals('', Server::getBasicAuthPwd());
    }

    /**
     * Test server methods exist.
     */
    public function testServerMethodsExist(): void
    {
        $methods = [
            'get', 'isSetted', 'getMethod', 'getHost', 'getUrl',
            'isBasicAuth', 'getBasicAuthUser', 'getBasicAuthPwd',
            'getUserAgent', 'getIp', 'isSsl', 'isAjax', 'getProtocol',
            'getPort', 'getAuthorizationHeaders'
        ];
        
        foreach ($methods as $method) {
            $this->assertTrue(method_exists(Server::class, $method), "Method {$method} should exist");
        }
    }

    /**
     * Test complex URL building.
     */
    public function testComplexUrlBuilding(): void
    {
        $_SERVER['HTTP_HOST'] = 'api.example.com';
        $_SERVER['REQUEST_URI'] = '/v1/users/123?include=profile';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '443';
        
        $url = Server::getUrl();
        $this->assertStringContains('api.example.com', $url);
        $this->assertStringContains('https', $url);
    }

    /**
     * Test edge cases.
     */
    public function testEdgeCases(): void
    {
        // Test with empty values
        $_SERVER['HTTP_HOST'] = '';
        $host = Server::getHost();
        $this->assertEquals('', $host);
        
        // Test with null-like values
        $_SERVER['HTTP_USER_AGENT'] = '';
        $ua = Server::getUserAgent();
        $this->assertEquals('', $ua);
    }
}
