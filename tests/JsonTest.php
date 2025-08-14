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
use FloatPHP\Classes\Filesystem\Json;

/**
 * Json class tests.
 */
final class JsonTest extends TestCase
{
    private static string $testDir;
    private static string $testJsonFile;

    public static function setUpBeforeClass(): void
    {
        self::$testDir = sys_get_temp_dir() . '/floatphp_json_test_' . uniqid();
        self::$testJsonFile = self::$testDir . '/test.json';
        
        mkdir(self::$testDir, 0777, true);
        
        $testData = ['name' => 'test', 'value' => 123, 'nested' => ['inner' => 'data']];
        file_put_contents(self::$testJsonFile, json_encode($testData));
    }

    public static function tearDownAfterClass(): void
    {
        if (file_exists(self::$testJsonFile)) {
            unlink(self::$testJsonFile);
        }
        if (is_dir(self::$testDir)) {
            rmdir(self::$testDir);
        }
    }

    /**
     * Test JSON file parsing.
     */
    public function testParse(): void
    {
        $result = Json::parse(self::$testJsonFile);
        
        $this->assertIsObject($result);
        $this->assertEquals('test', $result->name);
        $this->assertEquals(123, $result->value);
        $this->assertIsObject($result->nested);
        $this->assertEquals('data', $result->nested->inner);
    }

    /**
     * Test JSON file parsing as array.
     */
    public function testParseAsArray(): void
    {
        $result = Json::parse(self::$testJsonFile, true);
        
        $this->assertIsArray($result);
        $this->assertEquals('test', $result['name']);
        $this->assertEquals(123, $result['value']);
        $this->assertIsArray($result['nested']);
        $this->assertEquals('data', $result['nested']['inner']);
    }

    /**
     * Test JSON decoding.
     */
    public function testDecode(): void
    {
        $jsonString = '{"key":"value","number":42}';
        $result = Json::decode($jsonString);
        
        $this->assertIsObject($result);
        $this->assertEquals('value', $result->key);
        $this->assertEquals(42, $result->number);
    }

    /**
     * Test JSON decoding as array.
     */
    public function testDecodeAsArray(): void
    {
        $jsonString = '{"key":"value","number":42}';
        $result = Json::decode($jsonString, true);
        
        $this->assertIsArray($result);
        $this->assertEquals('value', $result['key']);
        $this->assertEquals(42, $result['number']);
    }

    /**
     * Test JSON encoding.
     */
    public function testEncode(): void
    {
        $data = ['key' => 'value', 'number' => 42];
        $result = Json::encode($data);
        
        $this->assertIsString($result);
        $decoded = json_decode($result, true);
        $this->assertEquals($data, $decoded);
    }

    /**
     * Test JSON formatting.
     */
    public function testFormat(): void
    {
        $data = ['key' => 'value', 'number' => 42];
        $result = Json::format($data);
        
        $this->assertIsString($result);
        $decoded = json_decode($result, true);
        $this->assertEquals($data, $decoded);
    }

    /**
     * Test JSON formatting with pretty print.
     */
    public function testFormatPretty(): void
    {
        $data = ['key' => 'value', 'nested' => ['inner' => 'data']];
        $result = Json::format($data, JSON_PRETTY_PRINT);
        
        $this->assertIsString($result);
        $this->assertStringContainsString("\n", $result); // Pretty print adds newlines
        $decoded = json_decode($result, true);
        $this->assertEquals($data, $decoded);
    }

    /**
     * Test JSON formatting with custom flags.
     */
    public function testFormatWithFlags(): void
    {
        $data = ['key' => 'value', 'unicode' => 'café'];
        $result = Json::format($data, JSON_UNESCAPED_UNICODE);
        
        $this->assertIsString($result);
        $this->assertStringContainsString('café', $result);
        $decoded = json_decode($result, true);
        $this->assertEquals($data, $decoded);
    }

    /**
     * Test invalid JSON decoding.
     */
    public function testDecodeInvalid(): void
    {
        $invalidJson = '{"invalid": json}';
        $result = Json::decode($invalidJson);
        
        $this->assertNull($result);
    }

    /**
     * Test empty JSON.
     */
    public function testDecodeEmpty(): void
    {
        $result = Json::decode('');
        $this->assertNull($result);
        
        $result = Json::decode('""');
        $this->assertEquals('', $result);
    }

    /**
     * Test encoding special values.
     */
    public function testEncodeSpecialValues(): void
    {
        // Test null
        $result = Json::encode(null);
        $this->assertEquals('null', $result);
        
        // Test boolean
        $result = Json::encode(true);
        $this->assertEquals('true', $result);
        
        $result = Json::encode(false);
        $this->assertEquals('false', $result);
        
        // Test numbers
        $result = Json::encode(123);
        $this->assertEquals('123', $result);
        
        $result = Json::encode(123.45);
        $this->assertEquals('123.45', $result);
    }

    /**
     * Test deep nested structures.
     */
    public function testDeepNested(): void
    {
        $data = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'level4' => 'deep value'
                    ]
                ]
            ]
        ];
        
        $encoded = Json::encode($data);
        $decoded = Json::decode($encoded, true);
        
        $this->assertEquals($data, $decoded);
        $this->assertEquals('deep value', $decoded['level1']['level2']['level3']['level4']);
    }

    /**
     * Test parsing non-existent file.
     */
    public function testParseNonExistentFile(): void
    {
        // This should trigger an error due to File::r() returning false for non-existent files
        // and Json::decode() expecting a string
        $this->expectException(\TypeError::class);
        Json::parse('/non/existent/file.json');
    }
}
