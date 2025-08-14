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
use FloatPHP\Classes\Filesystem\Stringify;

/**
 * Unit tests for Stringify class.
 */
class StringifyTest extends TestCase
{
    /**
     * Test replace method with strings.
     */
    public function testReplaceWithStrings() : void
    {
        $result = Stringify::replace('hello', 'hi', 'hello world');
        $this->assertEquals('hi world', $result);

        $result = Stringify::replace('foo', 'bar', 'foo foo foo');
        $this->assertEquals('bar bar bar', $result);
    }

    /**
     * Test replace method with arrays.
     */
    public function testReplaceWithArrays() : void
    {
        $result = Stringify::replace(['hello', 'world'], ['hi', 'universe'], 'hello world');
        $this->assertEquals('hi universe', $result);
    }

    /**
     * Test replace method with count parameter.
     */
    public function testReplaceWithCount() : void
    {
        $count = 0;
        $result = Stringify::replace('foo', 'bar', 'foo foo foo', $count);
        $this->assertEquals('bar bar bar', $result);
        $this->assertEquals(3, $count);
    }

    /**
     * Test subReplace method.
     */
    public function testSubReplace() : void
    {
        $result = Stringify::subReplace('hello world', 'hi', 0, 5);
        $this->assertEquals('hi world', $result);

        $result = Stringify::subReplace('hello world', 'universe', 6);
        $this->assertEquals('hello universe', $result);
    }

    /**
     * Test subCount method.
     */
    public function testSubCount() : void
    {
        $result = Stringify::subCount('hello hello hello', 'hello');
        $this->assertEquals(3, $result);

        $result = Stringify::subCount('hello hello hello', 'hello', 6);
        $this->assertEquals(2, $result);

        $result = Stringify::subCount('hello hello hello', 'hello', 0, 11);
        $this->assertEquals(2, $result);
    }

    /**
     * Test replaceArray method.
     */
    public function testReplaceArray() : void
    {
        $replace = [
            'hello' => 'hi',
            'world' => 'universe'
        ];
        $result = Stringify::replaceArray($replace, 'hello world');
        $this->assertEquals('hi universe', $result);
    }

    /**
     * Test replaceRegex method.
     */
    public function testReplaceRegex() : void
    {
        $result = Stringify::replaceRegex('/\d+/', 'X', 'abc123def456');
        $this->assertEquals('abcXdefX', $result);

        $result = Stringify::replaceRegex('/[aeiou]/', '*', 'hello');
        $this->assertEquals('h*ll*', $result);
    }

    /**
     * Test replaceRegexCb method.
     */
    public function testReplaceRegexCb() : void
    {
        $callback = function ($matches) {
            return strtoupper($matches[0]);
        };

        $result = Stringify::replaceRegexCb('/[a-z]/', $callback, 'hello');
        $this->assertEquals('HELLO', $result);
    }

    /**
     * Test remove method.
     */
    public function testRemove() : void
    {
        $result = Stringify::remove('hello', 'hello world');
        $this->assertEquals(' world', $result);

        $result = Stringify::remove(['hello', ' '], 'hello world');
        $this->assertEquals('world', $result);
    }

    /**
     * Test subRemove method.
     */
    public function testSubRemove() : void
    {
        $result = Stringify::subRemove('hello world', 0, 6);
        $this->assertEquals('world', $result);

        $result = Stringify::subRemove('hello world', 5);
        $this->assertEquals('hello', $result);
    }

    /**
     * Test removeRegex method.
     */
    public function testRemoveRegex() : void
    {
        $result = Stringify::removeRegex('/\d+/', 'abc123def456ghi');
        $this->assertEquals('abcdefghi', $result);

        $result = Stringify::removeRegex('/\s+/', 'hello   world  test');
        $this->assertEquals('helloworldtest', $result);
    }

    /**
     * Test repeat method.
     */
    public function testRepeat() : void
    {
        $result = Stringify::repeat('hello', 3);
        $this->assertEquals('hellohellohello', $result);

        $result = Stringify::repeat('x', 0);
        $this->assertEquals('', $result);
    }

    /**
     * Test lowercase method.
     */
    public function testLowercase() : void
    {
        $result = Stringify::lowercase('HELLO WORLD');
        $this->assertEquals('hello world', $result);

        $result = Stringify::lowercase('MixedCase123');
        $this->assertEquals('mixedcase123', $result);
    }

    /**
     * Test uppercase method.
     */
    public function testUppercase() : void
    {
        $result = Stringify::uppercase('hello world');
        $this->assertEquals('HELLO WORLD', $result);

        $result = Stringify::uppercase('mixedCase123');
        $this->assertEquals('MIXEDCASE123', $result);
    }

    /**
     * Test capitalize method.
     */
    public function testCapitalize() : void
    {
        $result = Stringify::capitalize('hello world');
        $this->assertEquals('Hello world', $result);

        $result = Stringify::capitalize('HELLO WORLD');
        $this->assertEquals('Hello world', $result);
    }

    /**
     * Test camelcase method.
     */
    public function testCamelcase() : void
    {
        $result = Stringify::camelcase('hello-world-test');
        $this->assertEquals('helloWorldTest', $result);

        $result = Stringify::camelcase('user_name');
        $this->assertEquals('userName', $result);
    }

    /**
     * Test slugify method.
     */
    public function testSlugify() : void
    {
        $result = Stringify::slugify('Hello World! This is a test.');
        $this->assertEquals('hello-world-this-is-a-test', $result);

        $result = Stringify::slugify('Special@#$%Characters');
        $this->assertEquals('special-characters', $result); // Fixed expectation
    }

    /**
     * Test getSpecialChars method.
     */
    public function testGetSpecialChars() : void
    {
        $result = Stringify::getSpecialChars();
        $this->assertIsArray($result);
        // Test should verify that it returns an array of special character mappings
    }

    /**
     * Test contains method with strings.
     */
    public function testContainsWithStrings() : void
    {
        $result = Stringify::contains('hello world', 'world');
        $this->assertTrue($result);

        $result = Stringify::contains('hello world', 'universe');
        $this->assertFalse($result);

        $result = Stringify::contains('hello world', '');
        $this->assertTrue($result); // Empty string should return true
    }

    /**
     * Test split method.
     */
    public function testSplit() : void
    {
        $result = Stringify::split('hello,world,test');
        $this->assertIsArray($result);
        // Add more specific assertions based on the method implementation
    }

    /**
     * Test chunk method.
     */
    public function testChunk() : void
    {
        $result = Stringify::chunk('hello world test', 5);
        $this->assertIsString($result);
        // Add more specific assertions based on expected chunking behavior
    }

    /**
     * Test encode method.
     */
    public function testEncode() : void
    {
        $result = Stringify::encode('hello world');
        $this->assertIsString($result);
        $this->assertEquals('hello world', $result); // Should return same for ASCII
    }

    /**
     * Test getEncoding method.
     */
    public function testGetEncoding() : void
    {
        $result = Stringify::getEncoding('hello world');
        $this->assertIsString($result);
    }

    /**
     * Test isUtf8 method.
     */
    public function testIsUtf8() : void
    {
        $result = Stringify::isUtf8('hello world');
        $this->assertTrue($result);

        $result = Stringify::isUtf8('héllo wörld');
        $this->assertTrue($result);

        // Test invalid UTF-8
        $invalidUtf8 = "\x80\x81";
        $result = Stringify::isUtf8($invalidUtf8);
        $this->assertFalse($result);
    }

    /**
     * Test formatPath method.
     */
    public function testFormatPath() : void
    {
        $result = Stringify::formatPath('/path/to/file/');
        $this->assertIsString($result);
        // Add assertions based on expected path formatting
    }

    /**
     * Test formatKey method.
     */
    public function testFormatKey() : void
    {
        $result = Stringify::formatKey('User-Name@#$');
        $this->assertEquals('user-name', $result);

        $result = Stringify::formatKey('SPECIAL123KEY');
        $this->assertEquals('special123key', $result);
    }

    /**
     * Test unSlash method.
     */
    public function testUnSlash() : void
    {
        $result = Stringify::unSlash('/path/to/file/');
        $this->assertEquals('pathtofile', $result);

        $result = Stringify::unSlash('no/slashes/here');
        $this->assertEquals('noslasheshere', $result);
    }

    /**
     * Test slash method.
     */
    public function testSlash() : void
    {
        $result = Stringify::slash('path');
        $this->assertEquals('/path', $result);

        $result = Stringify::slash('/already/slashed');
        $this->assertEquals('/alreadyslashed', $result);
    }

    /**
     * Test slash method with arrays.
     */
    public function testSlashWithArray() : void
    {
        $input = ['path1', 'path2', 'path3'];
        $result = Stringify::slash($input);
        $expected = ['/path1', '/path2', '/path3'];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test edge cases and error conditions.
     */
    public function testEdgeCases() : void
    {
        // Test with empty strings
        $result = Stringify::replace('', 'replacement', 'test');
        $this->assertEquals('test', $result);

        $result = Stringify::lowercase('');
        $this->assertEquals('', $result);

        $result = Stringify::uppercase('');
        $this->assertEquals('', $result);

        // Test with null-like values converted to strings
        $result = Stringify::contains('0', '0');
        $this->assertTrue($result);
    }

    /**
     * Test method chaining compatibility.
     */
    public function testMethodChaining() : void
    {
        // Test that methods can be chained through static calls
        $input = 'Hello World Test';
        $result = Stringify::lowercase(
            Stringify::replace(' ', '-', $input)
        );
        $this->assertEquals('hello-world-test', $result);
    }
}
