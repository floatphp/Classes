<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Connection Component Integration Tests
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
use FloatPHP\Classes\Connection\Db;
use \PDOException;

/**
 * Integration tests for Db class with real database operations.
 * Uses SQLite in-memory database for testing.
 */
class DbIntegrationTest extends TestCase
{
    /**
     * @var Db
     */
    private $db;

    /**
     * @var string
     */
    private $testDbPath = ':memory:';

    /**
     * Set up test environment before each test.
     */
    protected function setUp() : void
    {
        // Skip if SQLite is not available
        if ( !extension_loaded('pdo_sqlite') ) {
            $this->markTestSkipped('SQLite PDO extension is not available.');
        }

        // Create a real Db instance with SQLite in-memory database
        $config = [
            'driver'   => 'sqlite',
            'database' => $this->testDbPath
        ];

        try {
            $this->db = new class ($config) extends Db {
                protected function connect(array $config = []) : void
                {
                    $dsn = "sqlite:{$config['database']}";
                    $this->pdo = new \PDO($dsn);
                    $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
                    $this->isConnected = true;
                }
            };

            // Create test table
            $this->createTestTable();
        } catch (PDOException $e) {
            $this->markTestSkipped('Could not create test database: ' . $e->getMessage());
        }
    }

    /**
     * Create test table for integration tests.
     */
    private function createTestTable() : void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                age INTEGER,
                active BOOLEAN DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";

        $this->db->query($sql);
    }

    /**
     * Test real database INSERT operation.
     */
    public function testRealInsertOperation() : void
    {
        $sql = "INSERT INTO users (name, email, age, active) VALUES (:name, :email, :age, :active)";

        $result = $this->db->query($sql, [
            'name'   => 'John Doe',
            'email'  => 'john@example.com',
            'age'    => 30,
            'active' => true
        ]);

        $this->assertEquals(1, $result); // Should return 1 affected row

        // Test lastInsertId
        $lastId = $this->db->lastInsertId();
        $this->assertGreaterThan(0, $lastId);
    }

    /**
     * Test real database SELECT operation.
     */
    public function testRealSelectOperation() : void
    {
        // Insert test data first
        $this->insertTestUsers();

        $sql = "SELECT * FROM users WHERE active = :active ORDER BY name";
        $result = $this->db->query($sql, ['active' => true]);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Alice Smith', $result[0]['name']);
        $this->assertEquals('John Doe', $result[1]['name']);
    }

    /**
     * Test real database UPDATE operation.
     */
    public function testRealUpdateOperation() : void
    {
        // Insert test data first
        $this->insertTestUsers();

        $sql = "UPDATE users SET age = :age WHERE name = :name";
        $result = $this->db->query($sql, [
            'age'  => 35,
            'name' => 'John Doe'
        ]);

        $this->assertEquals(1, $result); // Should return 1 affected row

        // Verify the update
        $selectSql = "SELECT age FROM users WHERE name = :name";
        $updatedUser = $this->db->row($selectSql, ['name' => 'John Doe']);
        $this->assertEquals(35, $updatedUser['age']);
    }

    /**
     * Test real database DELETE operation.
     */
    public function testRealDeleteOperation() : void
    {
        // Insert test data first
        $this->insertTestUsers();

        $sql = "DELETE FROM users WHERE email = :email";
        $result = $this->db->query($sql, ['email' => 'inactive@example.com']);

        $this->assertEquals(1, $result); // Should return 1 affected row

        // Verify the deletion
        $countSql = "SELECT COUNT(*) FROM users";
        $count = $this->db->single($countSql);
        $this->assertEquals(2, $count); // Should have 2 users left
    }

    /**
     * Test row method with real data.
     */
    public function testRealRowMethod() : void
    {
        $this->insertTestUsers();

        $sql = "SELECT * FROM users WHERE email = :email";
        $result = $this->db->row($sql, ['email' => 'john@example.com']);

        $this->assertIsArray($result);
        $this->assertEquals('John Doe', $result['name']);
        $this->assertEquals('john@example.com', $result['email']);
        $this->assertEquals(30, $result['age']);
    }

    /**
     * Test single method with real data.
     */
    public function testRealSingleMethod() : void
    {
        $this->insertTestUsers();

        $sql = "SELECT COUNT(*) FROM users WHERE active = :active";
        $result = $this->db->single($sql, ['active' => true]);

        $this->assertEquals('2', $result); // SQLite returns string
    }

    /**
     * Test column method with real data.
     */
    public function testRealColumnMethod() : void
    {
        $this->insertTestUsers();

        $sql = "SELECT name FROM users WHERE active = :active ORDER BY name";
        $result = $this->db->column($sql, ['active' => true]);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(['Alice Smith', 'John Doe'], $result);
    }

    /**
     * Test transaction operations.
     */
    public function testRealTransactionOperations() : void
    {
        $this->assertTrue($this->db->beginTransaction());

        try {
            // Insert multiple users in a transaction
            $sql = "INSERT INTO users (name, email, age) VALUES (:name, :email, :age)";

            $this->db->query($sql, ['name' => 'User 1', 'email' => 'user1@example.com', 'age' => 25]);
            $this->db->query($sql, ['name' => 'User 2', 'email' => 'user2@example.com', 'age' => 28]);

            $this->assertTrue($this->db->executeTransaction());

            // Verify both users were inserted
            $count = $this->db->single("SELECT COUNT(*) FROM users");
            $this->assertEquals('2', $count);

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->fail('Transaction failed: ' . $e->getMessage());
        }
    }

    /**
     * Test transaction rollback.
     */
    public function testRealTransactionRollback() : void
    {
        // Insert one user first
        $this->db->query("INSERT INTO users (name, email) VALUES ('Initial User', 'initial@example.com')");

        $this->assertTrue($this->db->beginTransaction());

        try {
            // Insert a user
            $this->db->query("INSERT INTO users (name, email) VALUES ('Temp User', 'temp@example.com')");

            // Rollback the transaction
            $this->assertTrue($this->db->rollBack());

            // Verify only the initial user exists
            $count = $this->db->single("SELECT COUNT(*) FROM users");
            $this->assertEquals('1', $count);

            $user = $this->db->single("SELECT name FROM users");
            $this->assertEquals('Initial User', $user);

        } catch (\Exception $e) {
            $this->fail('Transaction rollback test failed: ' . $e->getMessage());
        }
    }

    /**
     * Test error handling with invalid SQL.
     */
    public function testRealErrorHandling() : void
    {
        $this->expectException(PDOException::class);
        $this->expectExceptionMessageMatches('/Query execution failed:/');

        $invalidSql = "SELECT * FROM non_existent_table";
        $this->db->query($invalidSql);
    }

    /**
     * Test parameter binding with various data types.
     */
    public function testRealParameterBinding() : void
    {
        $sql = "INSERT INTO users (name, email, age, active) VALUES (:name, :email, :age, :active)";

        // Test with different data types
        $result = $this->db->query($sql, [
            'name'   => 'Test User',
            'email'  => 'test@example.com',
            'age'    => null, // NULL value
            'active' => false // Boolean false
        ]);

        $this->assertEquals(1, $result);

        // Verify the data was inserted correctly
        $user = $this->db->row("SELECT * FROM users WHERE email = 'test@example.com'");
        $this->assertEquals('Test User', $user['name']);
        $this->assertNull($user['age']);
        $this->assertEquals(0, $user['active']); // SQLite stores boolean as integer
    }

    /**
     * Helper method to insert test users.
     */
    private function insertTestUsers() : void
    {
        $users = [
            ['name' => 'John Doe', 'email' => 'john@example.com', 'age' => 30, 'active' => true],
            ['name' => 'Alice Smith', 'email' => 'alice@example.com', 'age' => 25, 'active' => true],
            ['name' => 'Inactive User', 'email' => 'inactive@example.com', 'age' => 40, 'active' => false]
        ];

        $sql = "INSERT INTO users (name, email, age, active) VALUES (:name, :email, :age, :active)";

        foreach ($users as $user) {
            $this->db->query($sql, $user);
        }
    }

    /**
     * Clean up after each test.
     */
    protected function tearDown() : void
    {
        if ( $this->db ) {
            $this->db->close();
        }
        $this->db = null;
    }
}
