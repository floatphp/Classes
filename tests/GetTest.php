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
use FloatPHP\Classes\Http\Get;

/**
 * Get class tests.
 */
final class GetTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear GET data before each test
        $_GET = [];
    }

    protected function tearDown(): void
    {
        // Clear GET data after each test
        $_GET = [];
    }

    /**
     * Test get GET value.
     */
    public function testGetValue(): void
    {
        $_GET['test_key'] = 'test_value';
        
        $value = Get::get('test_key');
        $this->assertEquals('test_value', $value);
    }

    /**
     * Test get all GET values.
     */
    public function testGetAllValues(): void
    {
        $_GET['key1'] = 'value1';
        $_GET['key2'] = 'value2';
        
        $values = Get::get();
        $this->assertIsArray($values);
        $this->assertEquals('value1', $values['key1']);
        $this->assertEquals('value2', $values['key2']);
    }

    /**
     * Test get non-existent GET value.
     */
    public function testGetNonExistentValue(): void
    {
        $value = Get::get('non_existent');
        $this->assertNull($value);
    }

    /**
     * Test get when no GET values exist.
     */
    public function testGetWhenNoValuesExist(): void
    {
        $values = Get::get();
        $this->assertNull($values);
    }

    /**
     * Test set GET value.
     */
    public function testSetValue(): void
    {
        Get::set('new_key', 'new_value');
        
        $this->assertEquals('new_value', $_GET['new_key']);
    }

    /**
     * Test set GET value with null.
     */
    public function testSetValueWithNull(): void
    {
        Get::set('null_key');
        
        $this->assertNull($_GET['null_key']);
    }

    /**
     * Test set GET value with different types.
     */
    public function testSetValueWithDifferentTypes(): void
    {
        Get::set('string_key', 'string_value');
        Get::set('int_key', 123);
        Get::set('bool_key', true);
        Get::set('array_key', ['nested' => 'value']);
        
        $this->assertEquals('string_value', $_GET['string_key']);
        $this->assertEquals(123, $_GET['int_key']);
        $this->assertTrue($_GET['bool_key']);
        $this->assertIsArray($_GET['array_key']);
    }

    /**
     * Test check if GET value is set.
     */
    public function testIsSetted(): void
    {
        $_GET['test_key'] = 'test_value';
        
        $this->assertTrue(Get::isSetted('test_key'));
        $this->assertFalse(Get::isSetted('non_existent'));
    }

    /**
     * Test check if any GET values are set.
     */
    public function testIsSettedAny(): void
    {
        $this->assertFalse(Get::isSetted());
        
        $_GET['test_key'] = 'test_value';
        $this->assertTrue(Get::isSetted());
    }

    /**
     * Test unset specific GET value.
     */
    public function testUnsetSpecificValue(): void
    {
        $_GET['key1'] = 'value1';
        $_GET['key2'] = 'value2';
        
        Get::unset('key1');
        
        $this->assertFalse(isset($_GET['key1']));
        $this->assertTrue(isset($_GET['key2']));
    }

    /**
     * Test unset all GET values.
     */
    public function testUnsetAllValues(): void
    {
        $_GET['key1'] = 'value1';
        $_GET['key2'] = 'value2';
        
        Get::unset();
        
        $this->assertEmpty($_GET);
    }

    /**
     * Test GET value with empty string.
     */
    public function testGetValueWithEmptyString(): void
    {
        $_GET['empty_key'] = '';
        
        $value = Get::get('empty_key');
        $this->assertEquals('', $value);
        $this->assertTrue(Get::isSetted('empty_key'));
    }

    /**
     * Test GET value with zero.
     */
    public function testGetValueWithZero(): void
    {
        $_GET['zero_key'] = 0;
        
        $value = Get::get('zero_key');
        $this->assertEquals(0, $value);
        $this->assertTrue(Get::isSetted('zero_key'));
    }

    /**
     * Test GET value with false.
     */
    public function testGetValueWithFalse(): void
    {
        $_GET['false_key'] = false;
        
        $value = Get::get('false_key');
        $this->assertFalse($value);
        $this->assertTrue(Get::isSetted('false_key'));
    }

    /**
     * Test overwrite existing GET value.
     */
    public function testOverwriteExistingValue(): void
    {
        $_GET['existing_key'] = 'old_value';
        Get::set('existing_key', 'new_value');
        
        $this->assertEquals('new_value', $_GET['existing_key']);
    }

    /**
     * Test complex array structure.
     */
    public function testComplexArrayStructure(): void
    {
        $complex = [
            'level1' => [
                'level2' => [
                    'level3' => 'deep_value'
                ]
            ]
        ];
        
        Get::set('complex_key', $complex);
        $retrieved = Get::get('complex_key');
        
        $this->assertEquals('deep_value', $retrieved['level1']['level2']['level3']);
    }
}
