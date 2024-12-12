<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Connection Component
 * @version    : 1.4.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Connection;

use FloatPHP\Interfaces\Classes\LoggerInterface;
use FloatPHP\Classes\Filesystem\{TypeCheck, Stringify, Arrayify};
use \PDOException;
use \PDO;

/**
 * Advanced database manipulation.
 */
class Db
{
    /**
     * @access protected
     * @var object $pdo
     * @var object $query
     * @var bool $isConnected
     * @var array $parameters
     * @var object $logger
     */
    protected $pdo;
    protected $query;
    protected $isConnected = false;
    protected $parameters = [];
    protected $logger;

    /**
     * Connect to database.
     *
     * @access public
     * @param array $config
     * @param object LoggerInterface $logger
     */
    public function __construct(array $config = [], ?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->connect($config);
        $this->parameters = [];
    }

    /**
     * Close connection.
     *
     * @access public
     * @return void
     */
    public function close() : void
    {
        $this->pdo = null;
    }

    /**
     * Bind parameters.
     *
     * @access public
     * @param string $bind
     * @param mixed $value
     * @return void
     */
    public function bind(string $bind, $value = null) : void
    {
        $count = sizeof($this->parameters);
        $this->parameters[$count] = [":{$bind}", $value];
    }

    /**
     * Bind more parameters.
     *
     * @access public
     * @param array $bind
     * @return void
     */
    public function bindMore(array $bind) : void
    {
        if ( empty($this->parameters) ) {
            if ( TypeCheck::isArray($bind) ) {
                $columns = Arrayify::keys($bind);
                foreach ($columns as $i => &$column) {
                    $this->bind($column, $bind[$column]);
                }
            }
        }
    }

    /**
     * Get query result.
     * 
     * [FETCH_ASSOC : 2].
     *
     * @access public
     * @param string $sql
     * @param array $params
     * @param int $mode
     * @return mixed
     */
    public function query(string $sql, ?array $params = null, int $mode = 2) : mixed
    {
        // Format SQL
        $sql = Stringify::replace(search: "\r", replace: ' ', subject: $sql);
        $sql = trim($sql);

        // Init SQL
        $this->init($sql, $params);

        // Catch SQL statement
        if ( $this->getStatementType($sql) == 'read' ) {
            return $this->query->fetchAll($mode);
        }

        if ( $this->getStatementType($sql) == 'write' ) {
            return $this->query->rowCount();
        }

        return null;
    }

    /**
     * Get last inserted Id.
     *
     * @access public
     * @return mixed
     */
    public function lastInsertId() : mixed
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin transaction.
     *
     * @access public
     * @return bool
     */
    public function beginTransaction() : bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Execute transaction.
     *
     * @access public
     * @return bool
     */
    public function executeTransaction() : bool
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction.
     *
     * @access public
     * @return bool
     */
    public function rollBack() : bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Returns column from the result set.
     *
     * @access public
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public function column(string $sql, ?array $params = null) : mixed
    {
        $this->init($sql, $params);
        $columns = $this->query->fetchAll(PDO::FETCH_NUM);
        $column = null;
        foreach ($columns as $cells) {
            $column[] = $cells[0];
        }
        return $column;
    }

    /**
     * Returns row from the result set.
     *
     * [FETCH_ASSOC : 2].
     *
     * @access public
     * @param string $sql
     * @param array $params
     * @param int $mode
     * @return mixed
     */
    public function row(string $sql, ?array $params = null, int $mode = 2) : mixed
    {
        $this->init($sql, $params);
        $result = $this->query->fetch($mode);
        $this->query->closeCursor();
        return $result;
    }

    /**
     * Returns value of one single field (column).
     *
     * @access public
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public function single(string $sql, ?array $params = null) : mixed
    {
        $this->init($sql, $params);
        $result = $this->query->fetchColumn();
        $this->query->closeCursor();
        return $result;
    }

    /**
     * Connect to database.
     *
     * @access protected
     * @param array $config
     * @return void
     */
    protected function connect(array $config = []) : void
    {
        try {

            // Get configuration
            $config = Arrayify::merge([
                'name'    => '',
                'host'    => 'localhost',
                'port'    => 3306,
                'user'    => '',
                'pswd'    => '',
                'charset' => 'utf8'
            ], $config);

            extract($config);

            $dsn = "mysql:dbname={$name};host={$host};port={$port}";
            $this->pdo = new PDO($dsn, $user, $pswd, options: [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}"
            ]);

            // Log errors
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Disable prepare statement emulation
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            // Connection succeeded
            $this->isConnected = true;

        } catch (PDOException $e) {
            $message = $e->getMessage();
            $this->log($message);
            exit($message);
        }
    }

    /**
     * Init SQL query.
     *
     * @access protected
     * @param string $sql
     * @param array $params
     * @return void
     */
    protected function init(string $sql, array $params = []) : void
    {
        // Connect to database
        if ( !$this->isConnected ) {
            $this->connect();
        }

        try {

            // Prepare query
            $this->query = $this->pdo->prepare($sql);

            // Add bind parameters
            $this->bindMore($params);

            // Bind parameters
            if ( !empty($this->parameters) ) {
                foreach ($this->parameters as $param => $value) {
                    if ( TypeCheck::isInt($value[1]) ) {
                        $type = PDO::PARAM_INT;

                    } elseif ( TypeCheck::isBool($value[1]) ) {
                        $type = PDO::PARAM_BOOL;

                    } elseif ( TypeCheck::isNull($value[1]) ) {
                        $type = PDO::PARAM_NULL;

                    } else {
                        $type = PDO::PARAM_STR;
                    }
                    // Add type when binding the values to the column
                    $this->query->bindValue($value[0], $value[1], $type);
                }
            }

            // Execute SQL 
            $this->query->execute();

        } catch (PDOException $e) {
            // Write into log and display exception
            $message = $e->getMessage();
            $this->log($message, $sql);
            exit($message);
        }

        // Reset bind parameters
        $this->parameters = [];
    }

    /** 
     * Get query statement type.
     *
     * @access protected
     * @param string $sql
     * @return mixed
     */
    protected function getStatementType(string $sql = '') : mixed
    {
        $sql = Stringify::replaceRegex(regex: "/\s+|\t+|\n+/", replace: ' ', subject: $sql);
        $raw = explode(' ', $sql);
        $header = $raw[0] ?? '';
        $st = strtolower($header);

        if ( $st == 'select' || $st == 'show' ) {
            return 'read';
        }

        if ( $st == 'insert' || $st == 'update' || $st == 'delete' ) {
            return 'write';
        }

        return false;
    }

    /** 
     * Log error.
     *
     * @access protected
     * @param string $message
     * @param string $sql
     * @return string
     */
    protected function log(?string $message = null, string $sql = null) : string
    {
        if ( !$message ) {
            $message = 'Unhandled Exception';
        }

        if ( !empty($sql) ) {
            $message .= "\r\nRaw SQL : {$sql}";
        }

        if ( $this->logger ) {
            $this->logger->error($message);
        }

        return $message;
    }
}
