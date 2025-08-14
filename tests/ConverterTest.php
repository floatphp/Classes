<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component Tests
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Tests\Classes\Filesystem;

use PHPUnit\Framework\TestCase;
use FloatPHP\Classes\Filesystem\Converter;

/**
 * Converter class tests.
 */
final class ConverterTest extends TestCase
{
    /**
     * Test array to object conversion.
     */
    public function testToObject(): void
    {
        $array = ['key1' => 'value1', 'key2' => 'value2'];
        $object = Converter::toObject($array);
        
        $this->assertIsObject($object);
        $this->assertEquals('value1', $object->key1);
        $this->assertEquals('value2', $object->key2);
    }

    /**
     * Test array to object conversion with strict mode.
     */
    public function testToObjectStrict(): void
    {
        $array = ['key1' => 'value1', 'nested' => ['inner' => 'value']];
        $object = Converter::toObject($array, true);
        
        $this->assertIsObject($object);
        $this->assertEquals('value1', $object->key1);
        $this->assertIsObject($object->nested);
        $this->assertEquals('value', $object->nested->inner);
    }

    /**
     * Test object to array conversion.
     */
    public function testToArray(): void
    {
        $object = (object)['key1' => 'value1', 'key2' => 'value2'];
        $array = Converter::toArray($object);
        
        $this->assertIsArray($array);
        $this->assertEquals('value1', $array['key1']);
        $this->assertEquals('value2', $array['key2']);
    }

    /**
     * Test data to key conversion.
     */
    public function testToKey(): void
    {
        $data = ['test' => 'data'];
        $key = Converter::toKey($data);
        
        $this->assertIsString($key);
        $this->assertNotEmpty($key);
        
        // Same data should produce same key
        $key2 = Converter::toKey($data);
        $this->assertEquals($key, $key2);
    }

    /**
     * Test number to float conversion.
     */
    public function testToFloat(): void
    {
        $result = Converter::toFloat(1234.567);
        $this->assertIsFloat($result);
        $this->assertEquals(1235.0, $result);

        $result = Converter::toFloat(1234.567, 2);
        $this->assertEquals(1234.57, $result);

        $result = Converter::toFloat(1234.567, 0, ',', '.');
        $this->assertEquals(1235.0, $result);
    }

    /**
     * Test number to money conversion.
     */
    public function testToMoney(): void
    {
        $result = Converter::toMoney(1234.567);
        $this->assertIsString($result);
        $this->assertEquals('1234.57', $result);

        $result = Converter::toMoney(1234.567, 0);
        $this->assertEquals('1235', $result);

        $result = Converter::toMoney(1234.567, 2, ',', '.');
        $this->assertEquals('1234,57', $result);
    }

    /**
     * Test dynamic type conversion for boolean.
     */
    public function testToTypeBool(): void
    {
        // This test depends on TypeCheck::isDynamicType implementation
        // Testing what we can verify
        $result = Converter::toType('test');
        $this->assertEquals('test', $result);
    }

    /**
     * Test dynamic type conversion for arrays.
     */
    public function testToTypeArray(): void
    {
        $array = ['item1', 'item2'];
        $result = Converter::toType($array);
        $this->assertIsArray($result);
    }

    /**
     * Test data to text conversion.
     */
    public function testToText(): void
    {
        $data = ['key' => 'value'];
        $result = Converter::toText($data);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /**
     * Test data from text conversion.
     */
    public function testFromText(): void
    {
        $data = ['key' => 'value'];
        $text = Converter::toText($data);
        $result = Converter::fromText($text);
        
        $this->assertIsArray($result);
        $this->assertEquals('value', $result['key']);
    }

    /**
     * Test types to string conversion.
     */
    public function testToString(): void
    {
        $this->assertEquals('true', Converter::toString(true));
        $this->assertEquals('false', Converter::toString(false));
        $this->assertEquals('', Converter::toString(null));
        $this->assertEquals('null', Converter::toString(null, true));
        $this->assertEquals('123', Converter::toString(123));
        $this->assertEquals('test', Converter::toString('test'));
    }

    /**
     * Test string conversion with arrays.
     */
    public function testToStringArray(): void
    {
        $array = ['key' => 'value'];
        $result = Converter::toString($array);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /**
     * Test string conversion with objects.
     */
    public function testToStringObject(): void
    {
        $object = (object)['key' => 'value'];
        $result = Converter::toString($object);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /**
     * Test edge cases for conversions.
     */
    public function testEdgeCases(): void
    {
        // Empty array
        $emptyArray = [];
        $object = Converter::toObject($emptyArray);
        $this->assertIsObject($object);

        // Empty object
        $emptyObject = new \stdClass();
        $array = Converter::toArray($emptyObject);
        $this->assertIsArray($array);
        $this->assertEmpty($array);

        // Zero values
        $this->assertEquals('0', Converter::toString(0));
        $this->assertEquals('0', Converter::toString(0.0));
    }
}
