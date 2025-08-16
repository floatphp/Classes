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
use FloatPHP\Classes\Http\Cookie;

/**
 * Cookie class tests.
 */
final class CookieTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear cookies before each test
        $_COOKIE = [];
    }

    protected function tearDown(): void
    {
        // Clear cookies after each test
        $_COOKIE = [];
    }

    /**
     * Test get cookie value.
     */
    public function testGetCookie(): void
    {
        $_COOKIE['test_key'] = 'test_value';
        
        $value = Cookie::get('test_key');
        $this->assertEquals('test_value', $value);
    }

    /**
     * Test get all cookies.
     */
    public function testGetAllCookies(): void
    {
        $_COOKIE['key1'] = 'value1';
        $_COOKIE['key2'] = 'value2';
        
        $cookies = Cookie::get();
        $this->assertIsArray($cookies);
        $this->assertEquals('value1', $cookies['key1']);
        $this->assertEquals('value2', $cookies['key2']);
    }

    /**
     * Test get non-existent cookie.
     */
    public function testGetNonExistentCookie(): void
    {
        $value = Cookie::get('non_existent');
        $this->assertNull($value);
    }

    /**
     * Test get when no cookies exist.
     */
    public function testGetWhenNoCookiesExist(): void
    {
        $cookies = Cookie::get();
        $this->assertNull($cookies);
    }

    /**
     * Test check if cookie is set.
     */
    public function testIsSetted(): void
    {
        $_COOKIE['test_key'] = 'test_value';
        
        $this->assertTrue(Cookie::isSet('test_key'));
        $this->assertFalse(Cookie::isSet('non_existent'));
    }

    /**
     * Test check if any cookies are set.
     */
    public function testIsSettedAny(): void
    {
        $this->assertFalse(Cookie::isSet());
        
        $_COOKIE['test_key'] = 'test_value';
        $this->assertTrue(Cookie::isSet());
    }

    /**
     * Test unset specific cookie.
     */
    public function testUnsetSpecificCookie(): void
    {
        $_COOKIE['key1'] = 'value1';
        $_COOKIE['key2'] = 'value2';
        
        Cookie::unset('key1');
        
        $this->assertFalse(isset($_COOKIE['key1']));
        $this->assertTrue(isset($_COOKIE['key2']));
    }

    /**
     * Test unset all cookies.
     */
    public function testUnsetAllCookies(): void
    {
        $_COOKIE['key1'] = 'value1';
        $_COOKIE['key2'] = 'value2';
        
        Cookie::unset();
        
        $this->assertEmpty($_COOKIE);
    }

    /**
     * Test cookie set method exists.
     */
    public function testSetMethodExists(): void
    {
        $this->assertTrue(method_exists(Cookie::class, 'set'));
    }

    /**
     * Test clear method exists.
     */
    public function testClearMethodExists(): void
    {
        $this->assertTrue(method_exists(Cookie::class, 'clear'));
    }

    /**
     * Test cookie with empty value.
     */
    public function testCookieWithEmptyValue(): void
    {
        $_COOKIE['empty_key'] = '';
        
        $value = Cookie::get('empty_key');
        $this->assertEquals('', $value);
        $this->assertTrue(Cookie::isSet('empty_key'));
    }

    /**
     * Test cookie with null value.
     */
    public function testCookieWithNullValue(): void
    {
        $_COOKIE['null_key'] = null;
        
        $value = Cookie::get('null_key');
        $this->assertNull($value);
        $this->assertTrue(Cookie::isSet('null_key'));
    }

    /**
     * Test cookie with numeric value.
     */
    public function testCookieWithNumericValue(): void
    {
        $_COOKIE['numeric_key'] = 123;
        
        $value = Cookie::get('numeric_key');
        $this->assertEquals(123, $value);
    }

    /**
     * Test cookie with boolean value.
     */
    public function testCookieWithBooleanValue(): void
    {
        $_COOKIE['bool_key'] = true;
        
        $value = Cookie::get('bool_key');
        $this->assertTrue($value);
    }

    /**
     * Test cookie with array value.
     */
    public function testCookieWithArrayValue(): void
    {
        $_COOKIE['array_key'] = ['nested' => 'value'];
        
        $value = Cookie::get('array_key');
        $this->assertIsArray($value);
        $this->assertEquals('value', $value['nested']);
    }
}
