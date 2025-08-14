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
use FloatPHP\Classes\Http\Curl;

/**
 * Curl class tests.
 */
final class CurlTest extends TestCase
{
    /**
     * Test cURL constants.
     */
    public function testCurlConstants(): void
    {
        $this->assertEquals(CURLOPT_URL, Curl::URL);
        $this->assertEquals(CURLOPT_HEADER, Curl::HEADER);
        $this->assertEquals(CURLOPT_HTTPHEADER, Curl::HTTPHEADER);
        $this->assertEquals(CURLOPT_TIMEOUT, Curl::TIMEOUT);
        $this->assertEquals(CURLOPT_POST, Curl::POST);
        $this->assertEquals(CURLOPT_POSTFIELDS, Curl::POSTFIELDS);
        $this->assertEquals(CURLOPT_RETURNTRANSFER, Curl::RETURNTRANSFER);
        $this->assertEquals(CURLOPT_FOLLOWLOCATION, Curl::FOLLOWLOCATION);
        $this->assertEquals(CURLOPT_USERAGENT, Curl::USERAGENT);
    }

    /**
     * Test cURL info constants.
     */
    public function testCurlInfoConstants(): void
    {
        $this->assertEquals(CURLINFO_EFFECTIVE_URL, Curl::EFFECTIVEURL);
        $this->assertEquals(CURLINFO_TOTAL_TIME, Curl::TOTALTIME);
        $this->assertEquals(CURLINFO_HTTP_CODE, Curl::HTTPCODE);
    }

    /**
     * Test cURL multi constants.
     */
    public function testCurlMultiConstants(): void
    {
        $this->assertEquals(CURLM_OK, Curl::OK);
        $this->assertEquals(CURLM_INTERNAL_ERROR, Curl::INTERNALERROR);
        $this->assertEquals(CURLM_BAD_HANDLE, Curl::BADHANDLE);
        $this->assertEquals(CURLM_BAD_EASY_HANDLE, Curl::BADEASYHANDLE);
        $this->assertEquals(CURLM_OUT_OF_MEMORY, Curl::OUTOFMEMORY);
        $this->assertEquals(CURLM_ADDED_ALREADY, Curl::ALREADYADDED);
    }

    /**
     * Test cURL init.
     */
    public function testCurlInit(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init('https://httpbin.org/get');
        $this->assertInstanceOf(\CurlHandle::class, $handle);
        
        curl_close($handle);
    }

    /**
     * Test cURL init without URL.
     */
    public function testCurlInitWithoutUrl(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init();
        $this->assertInstanceOf(\CurlHandle::class, $handle);
        
        curl_close($handle);
    }

    /**
     * Test cURL exec.
     */
    public function testCurlExec(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init('https://httpbin.org/get');
        Curl::setOpt($handle, Curl::RETURNTRANSFER, true);
        
        $result = Curl::exec($handle);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        
        curl_close($handle);
    }

    /**
     * Test set cURL option.
     */
    public function testSetOpt(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init();
        $result = Curl::setOpt($handle, Curl::RETURNTRANSFER, true);
        $this->assertTrue($result);
        
        curl_close($handle);
    }

    /**
     * Test set multiple cURL options.
     */
    public function testSetOptArray(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init();
        $options = [
            Curl::RETURNTRANSFER => true,
            Curl::TIMEOUT => 30
        ];
        
        $result = Curl::setOpt($handle, $options);
        $this->assertTrue($result);
        
        curl_close($handle);
    }

    /**
     * Test get cURL info.
     */
    public function testGetInfo(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init('https://httpbin.org/get');
        Curl::setOpt($handle, Curl::RETURNTRANSFER, true);
        Curl::exec($handle);
        
        $info = Curl::getInfo($handle, Curl::HTTPCODE);
        $this->assertIsInt($info);
        
        curl_close($handle);
    }

    /**
     * Test get all cURL info.
     */
    public function testGetInfoAll(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init('https://httpbin.org/get');
        Curl::setOpt($handle, Curl::RETURNTRANSFER, true);
        Curl::exec($handle);
        
        $info = Curl::getInfo($handle);
        $this->assertIsArray($info);
        $this->assertArrayHasKey('http_code', $info);
        
        curl_close($handle);
    }

    /**
     * Test cURL error handling.
     */
    public function testGetError(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init();
        $error = Curl::getError($handle);
        $this->assertIsString($error);
        
        curl_close($handle);
    }

    /**
     * Test set timeout.
     */
    public function testSetTimeout(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init();
        $result = Curl::setTimeout($handle, 30);
        $this->assertTrue($result);
        
        curl_close($handle);
    }

    /**
     * Test set user agent.
     */
    public function testSetUserAgent(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init();
        $result = Curl::setUserAgent($handle, 'Test-Agent/1.0');
        $this->assertTrue($result);
        
        curl_close($handle);
    }

    /**
     * Test set method.
     */
    public function testSetMethod(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init();
        $result = Curl::setMethod($handle, 'POST');
        $this->assertTrue($result);
        
        curl_close($handle);
    }

    /**
     * Test set POST.
     */
    public function testSetPost(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init();
        $result = Curl::setPost($handle);
        $this->assertTrue($result);
        
        curl_close($handle);
    }

    /**
     * Test follow redirects.
     */
    public function testFollow(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init();
        $result = Curl::follow($handle);
        $this->assertTrue($result);
        
        curl_close($handle);
    }

    /**
     * Test return transfer.
     */
    public function testReturn(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init();
        $result = Curl::return($handle);
        $this->assertTrue($result);
        
        curl_close($handle);
    }

    /**
     * Test SSL verification.
     */
    public function testVerifyPeer(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init();
        $result = Curl::verifyPeer($handle, false);
        $this->assertTrue($result);
        
        curl_close($handle);
    }

    /**
     * Test host verification.
     */
    public function testVerifyHost(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init();
        $result = Curl::verifyHost($handle, false);
        $this->assertTrue($result);
        
        curl_close($handle);
    }

    /**
     * Test close handle.
     */
    public function testClose(): void
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL extension not available');
        }

        $handle = Curl::init();
        $this->assertNull(Curl::close($handle));
    }
}
