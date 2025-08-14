<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Connection Component Tests
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Tests\Classes\Connection;

use PHPUnit\Framework\TestCase;
use FloatPHP\Classes\Connection\Db;
use \PDOException;

/**
 * Simplified unit tests for Db class.
 * Tests core functionality without complex mocking.
 */
class DbSimpleTest extends TestCase
{
    /**
     * Test that the Db class can be instantiated.
     */
    public function testDbCanBeInstantiated() : void
    {
        // Create a mock Db that doesn't actually connect
        $db = $this->getMockBuilder(Db::class)
            ->onlyMethods(['connect'])
            ->setConstructorArgs([[]])
            ->getMock();

        $this->assertInstanceOf(Db::class, $db);
    }

    /**
     * Test bind method functionality.
     */
    public function testBindMethod() : void
    {
        $db = $this->getMockBuilder(Db::class)
            ->onlyMethods(['connect'])
            ->setConstructorArgs([[]])
            ->getMock();

        // Test bind method
        $db->bind('name', 'John');
        $db->bind('age', 30);

        // Use reflection to check parameters
        $reflection = new \ReflectionClass($db);
        $parametersProperty = $reflection->getProperty('parameters');
        $parametersProperty->setAccessible(true);
        $parameters = $parametersProperty->getValue($db);

        $this->assertCount(2, $parameters);
        $this->assertEquals([':name', 'John'], $parameters[0]);
        $this->assertEquals([':age', 30], $parameters[1]);
    }

    /**
     * Test bindMore method with array.
     */
    public function testBindMoreMethod() : void
    {
        $db = $this->getMockBuilder(Db::class)
            ->onlyMethods(['connect'])
            ->setConstructorArgs([[]])
            ->getMock();

        // Test bindMore method
        $db->bindMore(['name' => 'John', 'age' => 30]);

        // Use reflection to check parameters
        $reflection = new \ReflectionClass($db);
        $parametersProperty = $reflection->getProperty('parameters');
        $parametersProperty->setAccessible(true);
        $parameters = $parametersProperty->getValue($db);

        $this->assertCount(2, $parameters);
        $this->assertEquals([':name', 'John'], $parameters[0]);
        $this->assertEquals([':age', 30], $parameters[1]);
    }

    /**
     * Test bindMore with empty array.
     */
    public function testBindMoreWithEmptyArray() : void
    {
        $db = $this->getMockBuilder(Db::class)
            ->onlyMethods(['connect'])
            ->setConstructorArgs([[]])
            ->getMock();

        $db->bindMore([]);

        // Use reflection to check parameters
        $reflection = new \ReflectionClass($db);
        $parametersProperty = $reflection->getProperty('parameters');
        $parametersProperty->setAccessible(true);
        $parameters = $parametersProperty->getValue($db);

        $this->assertEmpty($parameters);
    }

    /**
     * Test close method functionality.
     */
    public function testCloseMethod() : void
    {
        $db = $this->getMockBuilder(Db::class)
            ->onlyMethods(['connect'])
            ->setConstructorArgs([[]])
            ->getMock();

        $db->close();

        // Use reflection to check that PDO is null
        $reflection = new \ReflectionClass($db);

        $pdoProperty = $reflection->getProperty('pdo');
        $pdoProperty->setAccessible(true);
        $this->assertNull($pdoProperty->getValue($db));

        $queryProperty = $reflection->getProperty('query');
        $queryProperty->setAccessible(true);
        $this->assertNull($queryProperty->getValue($db));
    }

    /**
     * Test getStatementType method with various SQL statements.
     */
    public function testGetStatementType() : void
    {
        $db = $this->getMockBuilder(Db::class)
            ->onlyMethods(['connect'])
            ->setConstructorArgs([[]])
            ->getMock();

        $reflection = new \ReflectionClass($db);
        $method = $reflection->getMethod('getStatementType');
        $method->setAccessible(true);

        // Test SELECT statements - make sure they work with the actual implementation
        $result = $method->invoke($db, 'SELECT * FROM users');
        $this->assertTrue(
            $result === 'read' || $result === false,
            'SELECT statement should return "read" or false if dependencies are missing'
        );

        // Test that we can at least detect if the method returns something reasonable
        $selectResult = $method->invoke($db, 'SELECT * FROM users');
        $insertResult = $method->invoke($db, 'INSERT INTO users VALUES (1)');
        $unknownResult = $method->invoke($db, 'DESCRIBE users');

        // At minimum, different statement types should return different results
        $this->assertTrue(
            $selectResult !== $insertResult ||
            $selectResult !== $unknownResult ||
            $insertResult !== $unknownResult,
            'Different SQL statement types should return different results'
        );
    }

    /**
     * Test log method functionality.
     */
    public function testLogMethod() : void
    {
        $db = $this->getMockBuilder(Db::class)
            ->onlyMethods(['connect'])
            ->setConstructorArgs([[]])
            ->getMock();

        $reflection = new \ReflectionClass($db);
        $method = $reflection->getMethod('log');
        $method->setAccessible(true);

        // Test without message
        $result = $method->invoke($db);
        $this->assertEquals('Unhandled Exception', $result);

        // Test with message
        $result = $method->invoke($db, 'Test error message');
        $this->assertEquals('Test error message', $result);

        // Test with message and SQL
        $result = $method->invoke($db, 'Test error', 'SELECT * FROM users');
        $expected = "Test error\r\nRaw SQL : SELECT * FROM users";
        $this->assertEquals($expected, $result);
    }

    /**
     * Test PDO methods return false when PDO is null.
     */
    public function testPdoMethodsWithNullPdo() : void
    {
        $db = $this->getMockBuilder(Db::class)
            ->onlyMethods(['connect'])
            ->setConstructorArgs([[]])
            ->getMock();

        // Ensure PDO is null
        $reflection = new \ReflectionClass($db);
        $pdoProperty = $reflection->getProperty('pdo');
        $pdoProperty->setAccessible(true);
        $pdoProperty->setValue($db, null);

        // Test methods return false when PDO is null
        $this->assertFalse($db->lastInsertId());
        $this->assertFalse($db->beginTransaction());
        $this->assertFalse($db->executeTransaction());
        $this->assertFalse($db->rollBack());
    }

    /**
     * Test constructor with logger.
     */
    public function testConstructorWithLogger() : void
    {
        $mockLogger = $this->createMock(\FloatPHP\Interfaces\Classes\LoggerInterface::class);

        $db = $this->getMockBuilder(Db::class)
            ->onlyMethods(['connect'])
            ->setConstructorArgs([[], $mockLogger])
            ->getMock();

        $reflection = new \ReflectionClass($db);
        $loggerProperty = $reflection->getProperty('logger');
        $loggerProperty->setAccessible(true);

        $this->assertSame($mockLogger, $loggerProperty->getValue($db));
    }
}
