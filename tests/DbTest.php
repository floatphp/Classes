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
 * This file is a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Tests\Classes\Connection;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use FloatPHP\Classes\Connection\Db;
use FloatPHP\Interfaces\Classes\LoggerInterface;
use \PDO;
use \PDOStatement;
use \PDOException;

/**
 * Unit tests for Db class.
 */
class DbTest extends TestCase
{
    /**
     * @var Db|MockObject
     */
    private $db;

    /**
     * @var PDO|MockObject
     */
    private $mockPdo;

    /**
     * @var PDOStatement|MockObject
     */
    private $mockStatement;

    /**
     * @var LoggerInterface|MockObject
     */
    private $mockLogger;

    /**
     * Set up test environment before each test.
     */
    protected function setUp() : void
    {
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStatement = $this->createMock(PDOStatement::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);

        // Create a partial mock of Db to override the connect method
        $this->db = $this->getMockBuilder(Db::class)
            ->onlyMethods(['connect'])
            ->setConstructorArgs([[], $this->mockLogger])
            ->getMock();

        // Set up the mocked PDO connection
        $reflection = new \ReflectionClass($this->db);
        $pdoProperty = $reflection->getProperty('pdo');
        $pdoProperty->setAccessible(true);
        $pdoProperty->setValue($this->db, $this->mockPdo);

        $connectedProperty = $reflection->getProperty('isConnected');
        $connectedProperty->setAccessible(true);
        $connectedProperty->setValue($this->db, true);
    }

    /**
     * Test constructor with default parameters.
     */
    public function testConstructorWithDefaults() : void
    {
        $db = $this->getMockBuilder(Db::class)
            ->onlyMethods(['connect'])
            ->setConstructorArgs([])
            ->getMock();

        $this->assertInstanceOf(Db::class, $db);
    }

    /**
     * Test constructor with logger.
     */
    public function testConstructorWithLogger() : void
    {
        $db = $this->getMockBuilder(Db::class)
            ->onlyMethods(['connect'])
            ->setConstructorArgs([[], $this->mockLogger])
            ->getMock();

        $reflection = new \ReflectionClass($db);
        $loggerProperty = $reflection->getProperty('logger');
        $loggerProperty->setAccessible(true);

        $this->assertSame($this->mockLogger, $loggerProperty->getValue($db));
    }

    /**
     * Test bind method adds parameters correctly.
     */
    public function testBind() : void
    {
        $this->db->bind('name', 'John');
        $this->db->bind('age', 30);

        $reflection = new \ReflectionClass($this->db);
        $parametersProperty = $reflection->getProperty('parameters');
        $parametersProperty->setAccessible(true);
        $parameters = $parametersProperty->getValue($this->db);

        $this->assertCount(2, $parameters);
        $this->assertEquals([':name', 'John'], $parameters[0]);
        $this->assertEquals([':age', 30], $parameters[1]);
    }

    /**
     * Test bindMore method with valid array.
     */
    public function testBindMoreWithValidArray() : void
    {
        $params = ['name' => 'John', 'age' => 30];
        $this->db->bindMore($params);

        $reflection = new \ReflectionClass($this->db);
        $parametersProperty = $reflection->getProperty('parameters');
        $parametersProperty->setAccessible(true);
        $parameters = $parametersProperty->getValue($this->db);

        $this->assertCount(2, $parameters);
        $this->assertEquals([':name', 'John'], $parameters[0]);
        $this->assertEquals([':age', 30], $parameters[1]);
    }

    /**
     * Test bindMore method with empty array.
     */
    public function testBindMoreWithEmptyArray() : void
    {
        $this->db->bindMore([]);

        $reflection = new \ReflectionClass($this->db);
        $parametersProperty = $reflection->getProperty('parameters');
        $parametersProperty->setAccessible(true);
        $parameters = $parametersProperty->getValue($this->db);

        $this->assertEmpty($parameters);
    }

    /**
     * Test query method with SELECT statement.
     */
    public function testQueryWithSelectStatement() : void
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $expectedResult = [['id' => 1, 'name' => 'John']];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute');

        $this->mockStatement->expects($this->once())
            ->method('fetchAll')
            ->with(2) // PDO::FETCH_ASSOC
            ->willReturn($expectedResult);

        $result = $this->db->query($sql, ['id' => 1]);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test query method with INSERT statement.
     */
    public function testQueryWithInsertStatement() : void
    {
        $sql = "INSERT INTO users (name, email) VALUES (:name, :email)";
        $expectedRowCount = 1;

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute');

        $this->mockStatement->expects($this->once())
            ->method('rowCount')
            ->willReturn($expectedRowCount);

        $result = $this->db->query($sql, ['name' => 'John', 'email' => 'john@example.com']);

        $this->assertEquals($expectedRowCount, $result);
    }

    /**
     * Test query method with unknown statement type.
     */
    public function testQueryWithUnknownStatement() : void
    {
        $sql = "DESCRIBE users";

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute');

        $result = $this->db->query($sql);

        $this->assertNull($result);
    }

    /**
     * Test lastInsertId method with valid PDO.
     */
    public function testLastInsertIdWithValidPdo() : void
    {
        $expectedId = '123';

        $this->mockPdo->expects($this->once())
            ->method('lastInsertId')
            ->willReturn($expectedId);

        $result = $this->db->lastInsertId();

        $this->assertEquals(123, $result); // Should be cast to int
    }

    /**
     * Test lastInsertId method with null PDO.
     */
    public function testLastInsertIdWithNullPdo() : void
    {
        $reflection = new \ReflectionClass($this->db);
        $pdoProperty = $reflection->getProperty('pdo');
        $pdoProperty->setAccessible(true);
        $pdoProperty->setValue($this->db, null);

        $result = $this->db->lastInsertId();

        $this->assertFalse($result);
    }

    /**
     * Test beginTransaction method.
     */
    public function testBeginTransaction() : void
    {
        $this->mockPdo->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);

        $result = $this->db->beginTransaction();

        $this->assertTrue($result);
    }

    /**
     * Test beginTransaction method with null PDO.
     */
    public function testBeginTransactionWithNullPdo() : void
    {
        $reflection = new \ReflectionClass($this->db);
        $pdoProperty = $reflection->getProperty('pdo');
        $pdoProperty->setAccessible(true);
        $pdoProperty->setValue($this->db, null);

        $result = $this->db->beginTransaction();

        $this->assertFalse($result);
    }

    /**
     * Test executeTransaction method.
     */
    public function testExecuteTransaction() : void
    {
        $this->mockPdo->expects($this->once())
            ->method('commit')
            ->willReturn(true);

        $result = $this->db->executeTransaction();

        $this->assertTrue($result);
    }

    /**
     * Test rollBack method.
     */
    public function testRollBack() : void
    {
        $this->mockPdo->expects($this->once())
            ->method('rollBack')
            ->willReturn(true);

        $result = $this->db->rollBack();

        $this->assertTrue($result);
    }

    /**
     * Test column method.
     */
    public function testColumn() : void
    {
        $sql = "SELECT name FROM users";
        $fetchResult = [['John'], ['Jane'], ['Bob']];
        $expectedResult = ['John', 'Jane', 'Bob'];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute');

        $this->mockStatement->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_NUM)
            ->willReturn($fetchResult);

        $result = $this->db->column($sql);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test row method.
     */
    public function testRow() : void
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $expectedResult = ['id' => 1, 'name' => 'John'];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute');

        $this->mockStatement->expects($this->once())
            ->method('fetch')
            ->with(2) // PDO::FETCH_ASSOC
            ->willReturn($expectedResult);

        $this->mockStatement->expects($this->once())
            ->method('closeCursor');

        $result = $this->db->row($sql, ['id' => 1]);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test single method.
     */
    public function testSingle() : void
    {
        $sql = "SELECT COUNT(*) FROM users";
        $expectedResult = '42';

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($this->mockStatement);

        $this->mockStatement->expects($this->once())
            ->method('execute');

        $this->mockStatement->expects($this->once())
            ->method('fetchColumn')
            ->willReturn($expectedResult);

        $this->mockStatement->expects($this->once())
            ->method('closeCursor');

        $result = $this->db->single($sql);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test close method.
     */
    public function testClose() : void
    {
        $this->db->close();

        $reflection = new \ReflectionClass($this->db);

        $pdoProperty = $reflection->getProperty('pdo');
        $pdoProperty->setAccessible(true);
        $this->assertNull($pdoProperty->getValue($this->db));

        $queryProperty = $reflection->getProperty('query');
        $queryProperty->setAccessible(true);
        $this->assertNull($queryProperty->getValue($this->db));
    }

    /**
     * Test getStatementType method with SELECT.
     */
    public function testGetStatementTypeWithSelect() : void
    {
        $reflection = new \ReflectionClass($this->db);
        $method = $reflection->getMethod('getStatementType');
        $method->setAccessible(true);

        $result = $method->invoke($this->db, 'SELECT * FROM users');
        $this->assertEquals('read', $result);

        $result = $method->invoke($this->db, '  select  name from users  ');
        $this->assertEquals('read', $result);
    }

    /**
     * Test getStatementType method with SHOW.
     */
    public function testGetStatementTypeWithShow() : void
    {
        $reflection = new \ReflectionClass($this->db);
        $method = $reflection->getMethod('getStatementType');
        $method->setAccessible(true);

        $result = $method->invoke($this->db, 'SHOW TABLES');
        $this->assertEquals('read', $result);
    }

    /**
     * Test getStatementType method with INSERT.
     */
    public function testGetStatementTypeWithInsert() : void
    {
        $reflection = new \ReflectionClass($this->db);
        $method = $reflection->getMethod('getStatementType');
        $method->setAccessible(true);

        $result = $method->invoke($this->db, 'INSERT INTO users (name) VALUES ("John")');
        $this->assertEquals('write', $result);
    }

    /**
     * Test getStatementType method with UPDATE.
     */
    public function testGetStatementTypeWithUpdate() : void
    {
        $reflection = new \ReflectionClass($this->db);
        $method = $reflection->getMethod('getStatementType');
        $method->setAccessible(true);

        $result = $method->invoke($this->db, 'UPDATE users SET name = "John" WHERE id = 1');
        $this->assertEquals('write', $result);
    }

    /**
     * Test getStatementType method with DELETE.
     */
    public function testGetStatementTypeWithDelete() : void
    {
        $reflection = new \ReflectionClass($this->db);
        $method = $reflection->getMethod('getStatementType');
        $method->setAccessible(true);

        $result = $method->invoke($this->db, 'DELETE FROM users WHERE id = 1');
        $this->assertEquals('write', $result);
    }

    /**
     * Test getStatementType method with unknown statement.
     */
    public function testGetStatementTypeWithUnknown() : void
    {
        $reflection = new \ReflectionClass($this->db);
        $method = $reflection->getMethod('getStatementType');
        $method->setAccessible(true);

        $result = $method->invoke($this->db, 'DESCRIBE users');
        $this->assertFalse($result);
    }

    /**
     * Test log method without message.
     */
    public function testLogWithoutMessage() : void
    {
        $reflection = new \ReflectionClass($this->db);
        $method = $reflection->getMethod('log');
        $method->setAccessible(true);

        $result = $method->invoke($this->db);

        $this->assertEquals('Unhandled Exception', $result);
    }

    /**
     * Test log method with message.
     */
    public function testLogWithMessage() : void
    {
        $reflection = new \ReflectionClass($this->db);
        $method = $reflection->getMethod('log');
        $method->setAccessible(true);

        $result = $method->invoke($this->db, 'Test error message');

        $this->assertEquals('Test error message', $result);
    }

    /**
     * Test log method with message and SQL.
     */
    public function testLogWithMessageAndSql() : void
    {
        $reflection = new \ReflectionClass($this->db);
        $method = $reflection->getMethod('log');
        $method->setAccessible(true);

        $result = $method->invoke($this->db, 'Test error', 'SELECT * FROM users');

        $expected = "Test error\r\nRaw SQL : SELECT * FROM users";
        $this->assertEquals($expected, $result);
    }

    /**
     * Test log method calls logger when available.
     */
    public function testLogCallsLogger() : void
    {
        $message = 'Test error message';

        $this->mockLogger->expects($this->once())
            ->method('error')
            ->with($message);

        $reflection = new \ReflectionClass($this->db);
        $method = $reflection->getMethod('log');
        $method->setAccessible(true);

        $method->invoke($this->db, $message);
    }

    /**
     * Test parameter binding with different data types.
     */
    public function testParameterBindingWithDifferentTypes() : void
    {
        $sql = "SELECT * FROM users WHERE id = :id AND active = :active AND name = :name AND deleted_at = :deleted";

        // Set up parameters
        $this->db->bind('id', 123);       // integer
        $this->db->bind('active', true);   // boolean
        $this->db->bind('name', 'John');   // string
        $this->db->bind('deleted', null);  // null

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($this->mockStatement);

        // Expect bindValue to be called with correct PDO parameter types
        $this->mockStatement->expects($this->exactly(4))
            ->method('bindValue')
            ->with(
                $this->logicalOr(':id', ':active', ':name', ':deleted'),
                $this->logicalOr(123, true, 'John', null),
                $this->logicalOr(PDO::PARAM_INT, PDO::PARAM_BOOL, PDO::PARAM_STR, PDO::PARAM_NULL)
            );

        $this->mockStatement->expects($this->once())
            ->method('execute');

        $this->mockStatement->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $this->db->query($sql);
    }

    /**
     * Test destructor calls close method.
     */
    public function testDestructorCallsClose() : void
    {
        $db = $this->getMockBuilder(Db::class)
            ->onlyMethods(['connect', 'close'])
            ->setConstructorArgs([[]])
            ->getMock();

        $db->expects($this->once())
            ->method('close');

        // Create reflection to access destructor
        $reflection = new \ReflectionClass($db);
        $destructor = $reflection->getMethod('__destruct');
        $destructor->invoke($db);
    }

    /**
     * Clean up after each test.
     */
    protected function tearDown() : void
    {
        $this->db = null;
        $this->mockPdo = null;
        $this->mockStatement = null;
        $this->mockLogger = null;
    }
}
