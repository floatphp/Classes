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
use FloatPHP\Classes\Http\Request;

/**
 * Request class tests.
 */
final class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear REQUEST data before each test
        $_REQUEST = [];
    }

    protected function tearDown(): void
    {
        // Clear REQUEST data after each test
        $_REQUEST = [];
    }

    /**
     * Test get REQUEST value.
     */
    public function testGetValue(): void
    {
        $_REQUEST['test_key'] = 'test_value';
        
        $value = Request::get('test_key');
        $this->assertEquals('test_value', $value);
    }

    /**
     * Test get all REQUEST values.
     */
    public function testGetAllValues(): void
    {
        $_REQUEST['key1'] = 'value1';
        $_REQUEST['key2'] = 'value2';
        
        $values = Request::get();
        $this->assertIsArray($values);
        $this->assertEquals('value1', $values['key1']);
        $this->assertEquals('value2', $values['key2']);
    }

    /**
     * Test get non-existent REQUEST value.
     */
    public function testGetNonExistentValue(): void
    {
        $value = Request::get('non_existent');
        $this->assertNull($value);
    }

    /**
     * Test get when no REQUEST values exist.
     */
    public function testGetWhenNoValuesExist(): void
    {
        $values = Request::get();
        $this->assertNull($values);
    }

    /**
     * Test set REQUEST value.
     */
    public function testSetValue(): void
    {
        Request::set('new_key', 'new_value');
        
        $this->assertEquals('new_value', $_REQUEST['new_key']);
    }

    /**
     * Test set REQUEST value with null.
     */
    public function testSetValueWithNull(): void
    {
        Request::set('null_key');
        
        $this->assertNull($_REQUEST['null_key']);
    }

    /**
     * Test set REQUEST value with different types.
     */
    public function testSetValueWithDifferentTypes(): void
    {
        Request::set('string_key', 'string_value');
        Request::set('int_key', 123);
        Request::set('bool_key', true);
        Request::set('array_key', ['nested' => 'value']);
        
        $this->assertEquals('string_value', $_REQUEST['string_key']);
        $this->assertEquals(123, $_REQUEST['int_key']);
        $this->assertTrue($_REQUEST['bool_key']);
        $this->assertIsArray($_REQUEST['array_key']);
    }

    /**
     * Test check if REQUEST value is set.
     */
    public function testIsSetted(): void
    {
        $_REQUEST['test_key'] = 'test_value';
        
        $this->assertTrue(Request::isSetted('test_key'));
        $this->assertFalse(Request::isSetted('non_existent'));
    }

    /**
     * Test check if any REQUEST values are set.
     */
    public function testIsSettedAny(): void
    {
        $this->assertFalse(Request::isSetted());
        
        $_REQUEST['test_key'] = 'test_value';
        $this->assertTrue(Request::isSetted());
    }

    /**
     * Test unset specific REQUEST value.
     */
    public function testUnsetSpecificValue(): void
    {
        $_REQUEST['key1'] = 'value1';
        $_REQUEST['key2'] = 'value2';
        
        Request::unset('key1');
        
        $this->assertFalse(isset($_REQUEST['key1']));
        $this->assertTrue(isset($_REQUEST['key2']));
    }

    /**
     * Test unset all REQUEST values.
     */
    public function testUnsetAllValues(): void
    {
        $_REQUEST['key1'] = 'value1';
        $_REQUEST['key2'] = 'value2';
        
        Request::unset();
        
        $this->assertEmpty($_REQUEST);
    }

    /**
     * Test REQUEST value with empty string.
     */
    public function testGetValueWithEmptyString(): void
    {
        $_REQUEST['empty_key'] = '';
        
        $value = Request::get('empty_key');
        $this->assertEquals('', $value);
        $this->assertTrue(Request::isSetted('empty_key'));
    }

    /**
     * Test REQUEST value with zero.
     */
    public function testGetValueWithZero(): void
    {
        $_REQUEST['zero_key'] = 0;
        
        $value = Request::get('zero_key');
        $this->assertEquals(0, $value);
        $this->assertTrue(Request::isSetted('zero_key'));
    }

    /**
     * Test REQUEST value with false.
     */
    public function testGetValueWithFalse(): void
    {
        $_REQUEST['false_key'] = false;
        
        $value = Request::get('false_key');
        $this->assertFalse($value);
        $this->assertTrue(Request::isSetted('false_key'));
    }

    /**
     * Test overwrite existing REQUEST value.
     */
    public function testOverwriteExistingValue(): void
    {
        $_REQUEST['existing_key'] = 'old_value';
        Request::set('existing_key', 'new_value');
        
        $this->assertEquals('new_value', $_REQUEST['existing_key']);
    }

    /**
     * Test mixed GET and POST simulation.
     */
    public function testMixedGetPostSimulation(): void
    {
        // Simulate mixed request data
        $_REQUEST['get_param'] = 'from_get';
        $_REQUEST['post_param'] = 'from_post';
        $_REQUEST['common_param'] = 'mixed_value';
        
        $this->assertEquals('from_get', Request::get('get_param'));
        $this->assertEquals('from_post', Request::get('post_param'));
        $this->assertEquals('mixed_value', Request::get('common_param'));
    }

    /**
     * Test form submission with various data types.
     */
    public function testFormSubmissionWithVariousTypes(): void
    {
        $_REQUEST['username'] = 'testuser';
        $_REQUEST['age'] = 25;
        $_REQUEST['is_active'] = true;
        $_REQUEST['tags'] = ['php', 'javascript', 'html'];
        $_REQUEST['profile'] = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com'
        ];
        
        $this->assertEquals('testuser', Request::get('username'));
        $this->assertEquals(25, Request::get('age'));
        $this->assertTrue(Request::get('is_active'));
        $this->assertIsArray(Request::get('tags'));
        $this->assertIsArray(Request::get('profile'));
        $this->assertEquals('John', Request::get('profile')['first_name']);
    }

    /**
     * Test URL parameters simulation.
     */
    public function testUrlParametersSimulation(): void
    {
        // Simulate URL parameters like: ?page=1&limit=10&search=test
        $_REQUEST['page'] = '1';
        $_REQUEST['limit'] = '10';
        $_REQUEST['search'] = 'test';
        $_REQUEST['sort'] = 'name';
        $_REQUEST['order'] = 'asc';
        
        $this->assertEquals('1', Request::get('page'));
        $this->assertEquals('10', Request::get('limit'));
        $this->assertEquals('test', Request::get('search'));
        $this->assertEquals('name', Request::get('sort'));
        $this->assertEquals('asc', Request::get('order'));
    }

    /**
     * Test special characters handling.
     */
    public function testSpecialCharactersHandling(): void
    {
        $specialData = [
            'unicode' => 'Héllo Wörld! 测试',
            'symbols' => '!@#$%^&*()_+-=[]{}|;:,.<>?',
            'quotes' => 'Single \'quotes\' and "double quotes"',
            'newlines' => "Line 1\nLine 2\rLine 3\r\nLine 4"
        ];
        
        foreach ($specialData as $key => $value) {
            Request::set($key, $value);
            $this->assertEquals($value, Request::get($key));
        }
    }

    /**
     * Test large data handling.
     */
    public function testLargeDataHandling(): void
    {
        $largeText = str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 500);
        Request::set('large_content', $largeText);
        
        $retrieved = Request::get('large_content');
        $this->assertEquals($largeText, $retrieved);
        $this->assertGreaterThan(25000, strlen($retrieved));
    }

    /**
     * Test nested array structure.
     */
    public function testNestedArrayStructure(): void
    {
        $nested = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'value' => 'deep_nested_value'
                    ]
                ]
            ]
        ];
        
        Request::set('nested_data', $nested);
        $retrieved = Request::get('nested_data');
        
        $this->assertEquals('deep_nested_value', $retrieved['level1']['level2']['level3']['value']);
    }
}
