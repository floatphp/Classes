<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Connection Component
 * @version    : 1.0.0
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Connection;

use FloatPHP\Interfaces\Classes\LoggerInterface;
use FloatPHP\Classes\Filesystem\TypeCheck;
use FloatPHP\Classes\Filesystem\Stringify;
use FloatPHP\Classes\Filesystem\Arrayify;
use \PDOException;
use \PDO;

class Db
{
    /**
     * @access private
     * @var object $pdo
     * @var bool $isConnected
     * @var array $parameters
     * @var object $logger
     */
    private $pdo = null;
    private $isConnected = false;
    private $parameters = [];
    private $logger = null;

    /**
     * @access protected
     * @var object $query
     */
    protected $query;

    /**
     * Connect to database
     *
     * @param array $config
     * @param object LoggerInterface $logger
     */
    public function __construct($config = [], LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->connect($config);
        $this->parameters = [];
    }
    
    /**
     * Close connection
     *
     * @access public
     * @param void
     * @return void
     */
    public function close()
    {
        $this->pdo = null;
    }

    /**
     * Bind params
     *
     * @access public
     * @param array $bind
     * @param string $value
     * @return void
     */
    public function bind($bind, $value)
    {
        $this->parameters[sizeof($this->parameters)] = [":{$bind}",$value];
    }

    /**
     * Bind more params
     *
     * @access public
     * @param array $bind
     * @return void
     */
    public function bindMore($bind)
    {
        if ( empty($this->parameters) ) {
            if ( TypeCheck::isArray($bind) ) {
                $columns = Arrayify::keys($bind);
                foreach ($columns as $i => &$column) {
                    $this->bind($column,$bind[$column]);
                }
            }
        }
    }

    /**
     * Query
     *
     * @access public
     * @param string $query
     * @param array $params
     * @param const $fetchmode
     * @return mixed
     */
    public function query($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $query = trim(Stringify::replace("\r",' ',$query));
        $this->Init($query,$params);
        $rawStatement = explode(' ',Stringify::replaceRegex("/\s+|\t+|\n+/",' ',$query));
        // Which SQL statement is used
        $statement = strtolower($rawStatement[0]);
        if ( $statement === 'select' || $statement === 'show' ) {
            return $this->query->fetchAll($fetchmode);
        } elseif ( $statement === 'insert' || $statement === 'update' || $statement === 'delete' ) {
            return $this->query->rowCount();
        }
        return null;
    }

    /**
     * Get last insert Id
     *
     * @access public
     * @param void
     * @return bool
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Starts the transaction
     *
     * @access public
     * @param void
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Execute Transaction
     *
     * @access public
     * @param void
     * @return bool
     */
    public function executeTransaction()
    {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback of Transaction
     *
     * @access public
     * @param void
     * @return bool
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }
    
    /**
     * Returns an array which represents a column from the result set 
     *
     * @access public
     * @param string $query
     * @param array $params
     * @return array
     */
    public function column($query, $params = null)
    {
        $this->Init($query,$params);
        $columns = $this->query->fetchAll(PDO::FETCH_NUM);
        $column = null;
        foreach ($columns as $cells) {
            $column[] = $cells[0];
        }
        return $column;
    }

    /**
     * Returns an array which represents a row from the result set 
     *
     * @access public
     * @param string $query
     * @param array $params
     * @param int $fetchmode
     * @return array
     */
    public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $this->Init($query,$params);
        $result = $this->query->fetch($fetchmode);
        $this->query->closeCursor(); 
        return $result;
    }

    /**
     *  Returns the value of one single field/column
     *
     * @access public
     * @param string $query
     * @param array $params
     * @return mixed
     */
    public function single($query, $params = null)
    {
        $this->Init($query,$params);
        $result = $this->query->fetchColumn();
        $this->query->closeCursor(); 
        return $result;
    }

    /**
     * Connect to database
     *
     * @access private
     * @param array $config
     * @return void
     */
    private function connect($config = [])
    {
        try {
            // Read settings & set PDO params
            $db = isset($config['db']) ? (string) $config['db'] : '';
            $host = isset($config['host']) ? (string) $config['host'] : 'localhost';
            $port = isset($config['port']) ? (int) $config['port'] : 3306;
            $user = isset($config['user']) ? (string) $config['user'] : 'root';
            $pswd = isset($config['pswd']) ? (string) $config['pswd'] : '';
            $charset = isset($config['charset']) ? (string) $config['charset'] : 'utf8';

            $dsn = "mysql:dbname={$db};host={$host};port={$port}";
            $this->pdo = new PDO($dsn,$user,$pswd,[
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}"
            ]);
            // log any exceptions on Fatal error
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Disable emulation of prepared statements
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
            // Connection succeeded
            $this->isConnected = true;
        } catch (PDOException $e) {
            // Write into logs
            echo $this->log($e->getMessage());
            die();
        }
    }

    /**
     * Every method which needs to execute a SQL query uses this method
     *
     * @access public
     * @param string $query
     * @param array $params
     * @return void
     * @throws PDOException
     */
    private function Init($query, $params = [])
    {
        // Connect to database
        if ( !$this->isConnected ) {
            $this->connect();
        }
        try {
            // Prepare query
            $this->query = $this->pdo->prepare($query);
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
                    $this->query->bindValue($value[0],$value[1],$type);
                }
            }
            // Execute SQL 
            $this->query->execute();
        } catch (PDOException $e) {
            // Write into log and display Exception
            echo $this->log($e->getMessage(),$query);
            die();
        }
        // Reset bind parameters
        $this->parameters = [];
    }

    /** 
     * Writes the log and returns the exception
     *
     * @access private
     * @param string $message
     * @param string $sql
     * @return string
     */
    private function log($message = '', $sql = '')
    {
        if ( empty($message) ) {
            $message = 'Unhandled Exception';
        }
        if ( !empty($sql) ) {
            $message .= "\r\nRaw SQL : {$sql}";
        }
        if ( $this->logger ) {
            $this->logger->error($message);
        }
        echo $message;
    }
}
