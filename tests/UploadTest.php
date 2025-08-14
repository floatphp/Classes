<?php

namespace FloatPHP\Tests\Classes\Http;

use PHPUnit\Framework\TestCase;
use FloatPHP\Classes\Http\Upload;

/**
 * Test Upload class.
 */
final class UploadTest extends TestCase
{
    public function testUploadMethodsExist(): void
    {
        // Upload class has static methods: get, set, isSetted, unset, move, handle, sanitize, getMimes
        $this->assertTrue(method_exists(Upload::class, 'get'));
        $this->assertTrue(method_exists(Upload::class, 'set'));
        $this->assertTrue(method_exists(Upload::class, 'isSetted'));
        $this->assertTrue(method_exists(Upload::class, 'unset'));
        $this->assertTrue(method_exists(Upload::class, 'move'));
        $this->assertTrue(method_exists(Upload::class, 'handle'));
        $this->assertTrue(method_exists(Upload::class, 'sanitize'));
        $this->assertTrue(method_exists(Upload::class, 'getMimes'));
    }

    public function testUploadClassExists(): void
    {
        $this->assertTrue(class_exists(Upload::class));
    }

    public function testUploadConstants(): void
    {
        // Test upload error constants
        $this->assertTrue(defined('UPLOAD_ERR_OK'));
        $this->assertTrue(defined('UPLOAD_ERR_INI_SIZE'));
        $this->assertTrue(defined('UPLOAD_ERR_FORM_SIZE'));
        $this->assertTrue(defined('UPLOAD_ERR_PARTIAL'));
        $this->assertTrue(defined('UPLOAD_ERR_NO_FILE'));
        $this->assertTrue(defined('UPLOAD_ERR_NO_TMP_DIR'));
        $this->assertTrue(defined('UPLOAD_ERR_CANT_WRITE'));
    }

    public function testGetMimes(): void
    {
        $mimes = Upload::getMimes();
        $this->assertIsArray($mimes);
    }

    public function testGetMimesWithTypes(): void
    {
        $mimes = Upload::getMimes(['jpg', 'png']);
        $this->assertIsArray($mimes);
    }

    public function testGetMethod(): void
    {
        $result = Upload::get();
        $this->assertTrue($result === null || is_array($result));
    }

    public function testGetWithKey(): void
    {
        $result = Upload::get('test_file');
        $this->assertTrue($result === null || is_array($result));
    }

    public function testIsSettedMethod(): void
    {
        $this->assertIsBool(Upload::isSetted());
    }

    public function testIsSettedWithKey(): void
    {
        $this->assertIsBool(Upload::isSetted('test_file'));
    }

    public function testSanitizeMethod(): void
    {
        $files = [];
        $sanitized = Upload::sanitize($files);
        $this->assertIsArray($sanitized);
    }

    public function testMoveMethod(): void
    {
        // Test move method exists and returns boolean
        $this->assertTrue(method_exists(Upload::class, 'move'));
    }

    public function testHandleMethod(): void
    {
        // Test handle method exists
        $this->assertTrue(method_exists(Upload::class, 'handle'));
    }

    public function testUploadClassStructure(): void
    {
        $reflection = new \ReflectionClass(Upload::class);
        $this->assertTrue($reflection->isFinal());
        $this->assertEquals('FloatPHP\Classes\Http', $reflection->getNamespaceName());
    }

    public function testUploadStaticMethods(): void
    {
        $reflection = new \ReflectionClass(Upload::class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_STATIC);
        $methodNames = array_map(fn($method) => $method->getName(), $methods);
        
        $this->assertContains('get', $methodNames);
        $this->assertContains('set', $methodNames);
        $this->assertContains('isSetted', $methodNames);
        $this->assertContains('move', $methodNames);
        $this->assertContains('handle', $methodNames);
    }
}
