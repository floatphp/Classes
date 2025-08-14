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
use FloatPHP\Classes\Filesystem\Exception;

/**
 * Exception class tests.
 */
final class ExceptionTest extends TestCase
{
    /**
     * Test shutdown handler registration.
     */
    public function testHandle(): void
    {
        $callback = function($args) {
            return 'shutdown_handled';
        };
        
        $result = Exception::handle($callback, ['test']);
        $this->assertTrue($result);
    }

    /**
     * Test getting last error.
     */
    public function testGetLastError(): void
    {
        // Clear any existing errors first
        Exception::clearLastError();
        
        // Trigger an error to test
        @trigger_error('Test error', E_USER_NOTICE);
        
        $error = Exception::getLastError();
        $this->assertNotNull($error);
        $this->assertIsArray($error);
        $this->assertArrayHasKey('message', $error);
        $this->assertEquals('Test error', $error['message']);
    }

    /**
     * Test clearing last error.
     */
    public function testClearLastError(): void
    {
        // Trigger an error first
        @trigger_error('Test error to clear', E_USER_NOTICE);
        
        // Clear the error
        Exception::clearLastError();
        
        // The error should be cleared
        $error = Exception::getLastError();
        $this->assertNull($error);
    }

    /**
     * Test triggering user error.
     */
    public function testTrigger(): void
    {
        // Clear any existing errors
        Exception::clearLastError();
        
        $result = @Exception::trigger('Test trigger error');
        $this->assertTrue($result);
        
        $error = Exception::getLastError();
        $this->assertNotNull($error);
        $this->assertEquals('Test trigger error', $error['message']);
    }

    /**
     * Test triggering user error with custom level.
     */
    public function testTriggerWithLevel(): void
    {
        Exception::clearLastError();
        
        $result = @Exception::trigger('Test custom level', E_USER_WARNING);
        $this->assertTrue($result);
        
        $error = Exception::getLastError();
        $this->assertNotNull($error);
        $this->assertEquals(E_USER_WARNING, $error['type']);
    }

    /**
     * Test logging user error.
     */
    public function testLog(): void
    {
        // Test basic logging
        $this->expectNotToPerformAssertions();
        Exception::log('Test log message');
    }

    /**
     * Test logging user error with type.
     */
    public function testLogWithType(): void
    {
        $this->expectNotToPerformAssertions();
        Exception::log('Test log message', 1);
    }

    /**
     * Test logging user error with path.
     */
    public function testLogWithPath(): void
    {
        $this->expectNotToPerformAssertions();
        Exception::log('Test log message', 0, '/tmp/test.log');
    }

    /**
     * Test logging user error with headers.
     */
    public function testLogWithHeaders(): void
    {
        $this->expectNotToPerformAssertions();
        Exception::log('Test log message', 0, null, ['Content-Type: text/plain']);
    }

    /**
     * Test exception inheritance.
     */
    public function testExceptionInheritance(): void
    {
        $this->assertTrue(is_subclass_of(Exception::class, \Exception::class));
    }

    /**
     * Test exception creation.
     */
    public function testExceptionCreation(): void
    {
        $exception = new Exception('Test exception');
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals('Test exception', $exception->getMessage());
    }

    /**
     * Test exception with code.
     */
    public function testExceptionWithCode(): void
    {
        $exception = new Exception('Test exception', 500);
        $this->assertEquals(500, $exception->getCode());
    }

    /**
     * Test handle with null args.
     */
    public function testHandleWithNullArgs(): void
    {
        $callback = function() {
            return 'no_args';
        };
        
        $result = Exception::handle($callback);
        $this->assertTrue($result);
    }

    /**
     * Test multiple error scenarios.
     */
    public function testMultipleErrors(): void
    {
        Exception::clearLastError();
        
        // First error
        @Exception::trigger('First error');
        $error1 = Exception::getLastError();
        
        // Second error should override first
        @Exception::trigger('Second error');
        $error2 = Exception::getLastError();
        
        $this->assertNotEquals($error1['message'], $error2['message']);
        $this->assertEquals('Second error', $error2['message']);
    }
}
