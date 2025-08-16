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
use FloatPHP\Classes\Http\Session;

/**
 * Session class tests.
 */
final class SessionTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear session data before each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        // Clear session data after each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }

    /**
     * Test session initialization.
     */
    public function testSessionInitialization(): void
    {
        $session = new Session();
        $this->assertInstanceOf(Session::class, $session);
    }

    /**
     * Test get session value.
     */
    public function testGetValue(): void
    {
        $_SESSION['test_key'] = 'test_value';
        
        $value = Session::get('test_key');
        $this->assertEquals('test_value', $value);
    }

    /**
     * Test get all session values.
     */
    public function testGetAllValues(): void
    {
        $_SESSION['key1'] = 'value1';
        $_SESSION['key2'] = 'value2';
        
        $values = Session::get();
        $this->assertIsArray($values);
        $this->assertEquals('value1', $values['key1']);
        $this->assertEquals('value2', $values['key2']);
    }

    /**
     * Test get non-existent session value.
     */
    public function testGetNonExistentValue(): void
    {
        $value = Session::get('non_existent');
        $this->assertNull($value);
    }

    /**
     * Test get when no session values exist.
     */
    public function testGetWhenNoValuesExist(): void
    {
        $values = Session::get();
        $this->assertNull($values);
    }

    /**
     * Test set session value.
     */
    public function testSetValue(): void
    {
        Session::set('new_key', 'new_value');
        
        $this->assertEquals('new_value', $_SESSION['new_key']);
    }

    /**
     * Test set session value with null.
     */
    public function testSetValueWithNull(): void
    {
        Session::set('null_key', null);
        
        $this->assertNull($_SESSION['null_key']);
    }

    /**
     * Test set session value with different types.
     */
    public function testSetValueWithDifferentTypes(): void
    {
        Session::set('string_key', 'string_value');
        Session::set('int_key', 123);
        Session::set('bool_key', true);
        Session::set('array_key', ['nested' => 'value']);
        
        $this->assertEquals('string_value', $_SESSION['string_key']);
        $this->assertEquals(123, $_SESSION['int_key']);
        $this->assertTrue($_SESSION['bool_key']);
        $this->assertIsArray($_SESSION['array_key']);
    }

    /**
     * Test check if session value is set.
     */
    public function testIsSetted(): void
    {
        $_SESSION['test_key'] = 'test_value';
        
        $this->assertTrue(Session::isSet('test_key'));
        $this->assertFalse(Session::isSet('non_existent'));
    }

    /**
     * Test check if any session values are set.
     */
    public function testIsSettedAny(): void
    {
        $this->assertFalse(Session::isSet());
        
        $_SESSION['test_key'] = 'test_value';
        $this->assertTrue(Session::isSet());
    }

    /**
     * Test unset specific session value.
     */
    public function testUnsetSpecificValue(): void
    {
        $_SESSION['key1'] = 'value1';
        $_SESSION['key2'] = 'value2';
        
        Session::unset('key1');
        
        $this->assertFalse(isset($_SESSION['key1']));
        $this->assertTrue(isset($_SESSION['key2']));
    }

    /**
     * Test unset all session values.
     */
    public function testUnsetAllValues(): void
    {
        $_SESSION['key1'] = 'value1';
        $_SESSION['key2'] = 'value2';
        
        Session::unset();
        
        $this->assertEmpty($_SESSION);
    }

    /**
     * Test session register.
     */
    public function testRegister(): void
    {
        Session::register(120);
        
        $this->assertTrue(Session::isSet('--session-id'));
        $this->assertTrue(Session::isSet('--session-time'));
        $this->assertTrue(Session::isSet('--session-start'));
    }

    /**
     * Test session is registered.
     */
    public function testIsRegistered(): void
    {
        $this->assertFalse(Session::isRegistered());
        
        Session::register();
        $this->assertTrue(Session::isRegistered());
    }

    /**
     * Test session methods exist.
     */
    public function testSessionMethodsExist(): void
    {
        $this->assertTrue(method_exists(Session::class, 'start'));
        $this->assertTrue(method_exists(Session::class, 'destroy'));
        $this->assertTrue(method_exists(Session::class, 'isActive'));
        $this->assertTrue(method_exists(Session::class, 'getId'));
        $this->assertTrue(method_exists(Session::class, 'getName'));
        $this->assertTrue(method_exists(Session::class, 'regenerateId'));
    }

    /**
     * Test session with complex data.
     */
    public function testSessionWithComplexData(): void
    {
        $complexData = [
            'user' => [
                'id' => 123,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'permissions' => ['read', 'write', 'admin'],
                'profile' => [
                    'avatar' => 'avatar.jpg',
                    'bio' => 'Software developer',
                    'settings' => [
                        'theme' => 'dark',
                        'language' => 'en'
                    ]
                ]
            ]
        ];
        
        Session::set('user_data', $complexData);
        $retrieved = Session::get('user_data');
        
        $this->assertEquals(123, $retrieved['user']['id']);
        $this->assertEquals('John Doe', $retrieved['user']['name']);
        $this->assertIsArray($retrieved['user']['permissions']);
        $this->assertEquals('dark', $retrieved['user']['profile']['settings']['theme']);
    }

    /**
     * Test session with empty values.
     */
    public function testSessionWithEmptyValues(): void
    {
        Session::set('empty_string', '');
        Session::set('zero_value', 0);
        Session::set('false_value', false);
        Session::set('empty_array', []);
        
        $this->assertEquals('', Session::get('empty_string'));
        $this->assertEquals(0, Session::get('zero_value'));
        $this->assertFalse(Session::get('false_value'));
        $this->assertEmpty(Session::get('empty_array'));
        
        // All should be considered "set" even if empty
        $this->assertTrue(Session::isSet('empty_string'));
        $this->assertTrue(Session::isSet('zero_value'));
        $this->assertTrue(Session::isSet('false_value'));
        $this->assertTrue(Session::isSet('empty_array'));
    }

    /**
     * Test session security data.
     */
    public function testSessionSecurityData(): void
    {
        // Simulate security-related session data
        Session::set('csrf_token', 'abc123def456');
        Session::set('user_agent', 'Mozilla/5.0...');
        Session::set('ip_address', '192.168.1.1');
        Session::set('login_time', time());
        Session::set('last_activity', time());
        
        $this->assertEquals('abc123def456', Session::get('csrf_token'));
        $this->assertIsString(Session::get('user_agent'));
        $this->assertEquals('192.168.1.1', Session::get('ip_address'));
        $this->assertIsInt(Session::get('login_time'));
        $this->assertIsInt(Session::get('last_activity'));
    }

    /**
     * Test session flash data simulation.
     */
    public function testSessionFlashDataSimulation(): void
    {
        // Simulate flash messages
        Session::set('flash_success', 'Data saved successfully!');
        Session::set('flash_error', 'An error occurred.');
        Session::set('flash_warning', 'Please check your input.');
        Session::set('flash_info', 'Information updated.');
        
        $this->assertEquals('Data saved successfully!', Session::get('flash_success'));
        $this->assertEquals('An error occurred.', Session::get('flash_error'));
        $this->assertEquals('Please check your input.', Session::get('flash_warning'));
        $this->assertEquals('Information updated.', Session::get('flash_info'));
    }

    /**
     * Test session shopping cart simulation.
     */
    public function testSessionShoppingCartSimulation(): void
    {
        $cart = [
            'items' => [
                ['id' => 1, 'name' => 'Product 1', 'price' => 29.99, 'quantity' => 2],
                ['id' => 2, 'name' => 'Product 2', 'price' => 49.99, 'quantity' => 1]
            ],
            'total' => 109.97,
            'currency' => 'USD',
            'coupon' => 'SAVE10',
            'discount' => 10.00
        ];
        
        Session::set('shopping_cart', $cart);
        $retrievedCart = Session::get('shopping_cart');
        
        $this->assertCount(2, $retrievedCart['items']);
        $this->assertEquals(109.97, $retrievedCart['total']);
        $this->assertEquals('SAVE10', $retrievedCart['coupon']);
    }

    /**
     * Test large session data.
     */
    public function testLargeSessionData(): void
    {
        $largeData = str_repeat('Lorem ipsum dolor sit amet. ', 1000);
        Session::set('large_content', $largeData);
        
        $retrieved = Session::get('large_content');
        $this->assertEquals($largeData, $retrieved);
        $this->assertGreaterThan(25000, strlen($retrieved));
    }
}
