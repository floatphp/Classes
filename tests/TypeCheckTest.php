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
use FloatPHP\Classes\Filesystem\TypeCheck;

/**
 * TypeCheck class tests.
 */
final class TypeCheckTest extends TestCase
{
    /**
     * Test string checking.
     */
    public function testIsString(): void
    {
        $this->assertTrue(TypeCheck::isString('hello'));
        $this->assertTrue(TypeCheck::isString(''));
        $this->assertFalse(TypeCheck::isString(123));
        $this->assertFalse(TypeCheck::isString(null));
        $this->assertFalse(TypeCheck::isString([]));
        $this->assertFalse(TypeCheck::isString(true));
    }

    /**
     * Test object checking.
     */
    public function testIsObject(): void
    {
        $object = new \stdClass();
        $this->assertTrue(TypeCheck::isObject($object));
        $this->assertFalse(TypeCheck::isObject('string'));
        $this->assertFalse(TypeCheck::isObject(123));
        $this->assertFalse(TypeCheck::isObject([]));
    }

    /**
     * Test object checking with specific class.
     */
    public function testIsObjectWithClass(): void
    {
        $object = new \stdClass();
        $this->assertTrue(TypeCheck::isObject($object, \stdClass::class));
        $this->assertFalse(TypeCheck::isObject($object, \Exception::class));
        
        $exception = new \Exception();
        $this->assertTrue(TypeCheck::isObject($exception, \Exception::class));
        $this->assertFalse(TypeCheck::isObject($exception, \stdClass::class));
    }

    /**
     * Test array checking.
     */
    public function testIsArray(): void
    {
        $this->assertTrue(TypeCheck::isArray([]));
        $this->assertTrue(TypeCheck::isArray([1, 2, 3]));
        $this->assertTrue(TypeCheck::isArray(['key' => 'value']));
        $this->assertFalse(TypeCheck::isArray('string'));
        $this->assertFalse(TypeCheck::isArray(123));
        $this->assertFalse(TypeCheck::isArray(null));
        $this->assertFalse(TypeCheck::isArray(new \stdClass()));
    }

    /**
     * Test iterator checking.
     */
    public function testIsIterator(): void
    {
        $this->assertTrue(TypeCheck::isIterator([]));
        $this->assertTrue(TypeCheck::isIterator([1, 2, 3]));
        $this->assertFalse(TypeCheck::isIterator('string'));
        $this->assertFalse(TypeCheck::isIterator(123));
        $this->assertFalse(TypeCheck::isIterator(null));
        $this->assertFalse(TypeCheck::isIterator(new \stdClass()));
    }

    /**
     * Test integer checking.
     */
    public function testIsInt(): void
    {
        $this->assertTrue(TypeCheck::isInt(123));
        $this->assertTrue(TypeCheck::isInt(0));
        $this->assertTrue(TypeCheck::isInt(-123));
        $this->assertFalse(TypeCheck::isInt('123'));
        $this->assertFalse(TypeCheck::isInt(123.45));
        $this->assertFalse(TypeCheck::isInt(null));
        $this->assertFalse(TypeCheck::isInt(true));
    }

    /**
     * Test numeric checking.
     */
    public function testIsNumeric(): void
    {
        $this->assertTrue(TypeCheck::isNumeric(123));
        $this->assertTrue(TypeCheck::isNumeric('123'));
        $this->assertTrue(TypeCheck::isNumeric('123.45'));
        $this->assertTrue(TypeCheck::isNumeric(123.45));
        $this->assertTrue(TypeCheck::isNumeric('0'));
        $this->assertTrue(TypeCheck::isNumeric('-123'));
        $this->assertFalse(TypeCheck::isNumeric('abc'));
        $this->assertFalse(TypeCheck::isNumeric(null));
        $this->assertFalse(TypeCheck::isNumeric([]));
        $this->assertFalse(TypeCheck::isNumeric(true));
    }

    /**
     * Test float checking.
     */
    public function testIsFloat(): void
    {
        $this->assertTrue(TypeCheck::isFloat(123.45));
        $this->assertTrue(TypeCheck::isFloat(0.0));
        $this->assertTrue(TypeCheck::isFloat(-123.45));
        $this->assertFalse(TypeCheck::isFloat(123));
        $this->assertFalse(TypeCheck::isFloat('123.45'));
        $this->assertFalse(TypeCheck::isFloat(null));
        $this->assertFalse(TypeCheck::isFloat(true));
    }

    /**
     * Test boolean checking.
     */
    public function testIsBool(): void
    {
        $this->assertTrue(TypeCheck::isBool(true));
        $this->assertTrue(TypeCheck::isBool(false));
        $this->assertFalse(TypeCheck::isBool(1));
        $this->assertFalse(TypeCheck::isBool(0));
        $this->assertFalse(TypeCheck::isBool('true'));
        $this->assertFalse(TypeCheck::isBool('false'));
        $this->assertFalse(TypeCheck::isBool(null));
    }

    /**
     * Test true checking.
     */
    public function testIsTrue(): void
    {
        $this->assertTrue(TypeCheck::isTrue(true));
        $this->assertFalse(TypeCheck::isTrue(false));
        $this->assertFalse(TypeCheck::isTrue(1));
        $this->assertFalse(TypeCheck::isTrue('true'));
        $this->assertFalse(TypeCheck::isTrue(null));
    }

    /**
     * Test false checking.
     */
    public function testIsFalse(): void
    {
        $this->assertTrue(TypeCheck::isFalse(false));
        $this->assertFalse(TypeCheck::isFalse(true));
        $this->assertFalse(TypeCheck::isFalse(0));
        $this->assertFalse(TypeCheck::isFalse('false'));
        $this->assertFalse(TypeCheck::isFalse(null));
    }

    /**
     * Test null checking.
     */
    public function testIsNull(): void
    {
        $this->assertTrue(TypeCheck::isNull(null));
        $this->assertFalse(TypeCheck::isNull(false));
        $this->assertFalse(TypeCheck::isNull(0));
        $this->assertFalse(TypeCheck::isNull(''));
        $this->assertFalse(TypeCheck::isNull([]));
    }

    /**
     * Test empty checking.
     */
    public function testIsEmpty(): void
    {
        $this->assertTrue(TypeCheck::isEmpty(''));
        $this->assertTrue(TypeCheck::isEmpty([]));
        $this->assertTrue(TypeCheck::isEmpty(null));
        $this->assertTrue(TypeCheck::isEmpty(false));
        $this->assertTrue(TypeCheck::isEmpty(0));
        $this->assertFalse(TypeCheck::isEmpty('hello'));
        $this->assertFalse(TypeCheck::isEmpty([1, 2, 3]));
        $this->assertFalse(TypeCheck::isEmpty(true));
        $this->assertFalse(TypeCheck::isEmpty(123));
    }

    /**
     * Test function checking.
     */
    public function testIsFunction(): void
    {
        $this->assertTrue(TypeCheck::isFunction('strlen'));
        $this->assertTrue(TypeCheck::isFunction('array_map'));
        $this->assertFalse(TypeCheck::isFunction('nonexistent_function'));
        $this->assertFalse(TypeCheck::isFunction(''));
        $this->assertFalse(TypeCheck::isFunction(123));
    }

    /**
     * Test class checking.
     */
    public function testIsClass(): void
    {
        $this->assertTrue(TypeCheck::isClass(\stdClass::class));
        $this->assertTrue(TypeCheck::isClass(\Exception::class));
        $this->assertFalse(TypeCheck::isClass('NonExistentClass'));
        $this->assertFalse(TypeCheck::isClass(''));
        $this->assertFalse(TypeCheck::isClass(123));
    }

    /**
     * Test resource checking.
     */
    public function testIsResource(): void
    {
        $resource = fopen('php://memory', 'r');
        $this->assertTrue(TypeCheck::isResource($resource));
        fclose($resource);
        
        $this->assertFalse(TypeCheck::isResource('string'));
        $this->assertFalse(TypeCheck::isResource(123));
        $this->assertFalse(TypeCheck::isResource(null));
        $this->assertFalse(TypeCheck::isResource([]));
    }

    /**
     * Test multiple type checking combinations.
     */
    public function testComplexTypeChecking(): void
    {
        $data = [
            'string' => 'hello',
            'int' => 123,
            'float' => 123.45,
            'bool' => true,
            'null' => null,
            'array' => [1, 2, 3],
            'object' => new \stdClass()
        ];

        $this->assertTrue(TypeCheck::isString($data['string']));
        $this->assertTrue(TypeCheck::isInt($data['int']));
        $this->assertTrue(TypeCheck::isFloat($data['float']));
        $this->assertTrue(TypeCheck::isBool($data['bool']));
        $this->assertTrue(TypeCheck::isNull($data['null']));
        $this->assertTrue(TypeCheck::isArray($data['array']));
        $this->assertTrue(TypeCheck::isObject($data['object']));
    }

    /**
     * Test edge cases.
     */
    public function testEdgeCases(): void
    {
        // Test empty string vs null
        $this->assertTrue(TypeCheck::isString(''));
        $this->assertFalse(TypeCheck::isString(null));
        
        // Test zero values
        $this->assertTrue(TypeCheck::isInt(0));
        $this->assertTrue(TypeCheck::isFloat(0.0));
        $this->assertTrue(TypeCheck::isFalse(false));
        
        // Test numeric strings
        $this->assertTrue(TypeCheck::isString('123'));
        $this->assertTrue(TypeCheck::isNumeric('123'));
        $this->assertFalse(TypeCheck::isInt('123'));
    }
}
