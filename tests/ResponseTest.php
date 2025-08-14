<?php

namespace FloatPHP\Tests\Classes\Http;

use PHPUnit\Framework\TestCase;
use FloatPHP\Classes\Http\Response;

/**
 * Test Response class.
 */
final class ResponseTest extends TestCase
{
    public function testResponseMethodsExist(): void
    {
        // Response class has static methods: set, setHttpHeader
        $this->assertTrue(method_exists(Response::class, 'set'));
        $this->assertTrue(method_exists(Response::class, 'setHttpHeader'));
    }

    public function testResponseClassExists(): void
    {
        $this->assertTrue(class_exists(Response::class));
    }

    public function testSetHttpHeaderMethodExists(): void
    {
        $this->assertTrue(method_exists(Response::class, 'setHttpHeader'));
    }

    public function testResponseExtendsStatus(): void
    {
        $this->assertTrue(is_subclass_of(Response::class, 'FloatPHP\Classes\Http\Status'));
    }

    public function testResponseConstants(): void
    {
        $this->assertTrue(defined('FloatPHP\Classes\Http\Response::TYPE'));
        $this->assertEquals('application/json', Response::TYPE);
    }

    public function testResponseStaticMethods(): void
    {
        // Test that static methods exist
        $reflection = new \ReflectionClass(Response::class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_STATIC);
        $methodNames = array_map(fn($method) => $method->getName(), $methods);
        
        $this->assertContains('set', $methodNames);
        $this->assertContains('setHttpHeader', $methodNames);
    }

    public function testResponseInheritance(): void
    {
        // Response extends Status and should have access to getMessage
        $this->assertTrue(method_exists(Response::class, 'getMessage'));
    }

    public function testResponseClassStructure(): void
    {
        $reflection = new \ReflectionClass(Response::class);
        $this->assertTrue($reflection->isFinal());
        $this->assertEquals('FloatPHP\Classes\Http', $reflection->getNamespaceName());
    }
}
