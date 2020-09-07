<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Connection Component
 * @version   : 1.1.0
 * @category  : PHP framework
 * @copyright : (c) JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace floatPHP\Classes\Connection;

use floatPHP\Classes\Filesystem\Logger;
use \PDOException;
use \PDO;

class Db
{
    /**
     * @access private
     * @var object $pdo
     * @var array $parameters
     * @var boolean $isConnected false
     * @var object $log
     */
    private $pdo;
    private $isConnected = false;
    private $log;
    private $parameters;

    /**
     * @access protected
     * @var object $query
     */
    protected $query;

    /**
     * Connect to database
     *
     * @param array $config
     * @return void
     */
    public function __construct($config = [])
    {
        $this->log = new Logger();
        $this->connect($config);
        $this->parameters = [];
    }

    /**
     * Connect to database
     *
     * @param array $config
     * @return void
     */
    private function connect($config = [])
    {
        $config = parse_ini_file(dirname(dirname(dirname(__FILE__))) . "/secret.ini");
        try {

            // Read settings & set PDO params
            $dsn = "mysql:dbname={$config['db']};host={$config['host']};port={$config['port']}";
            $this->pdo = new PDO($dsn,$config['user'], $config['pswd'], [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
            ]);

            // log any exceptions on Fatal error
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Disable emulation of prepared statements, use REAL prepared statements instead
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            // Connection succeeded, set the boolean to true
            $this->isConnected = true;

        } catch (PDOException $e) {
            // Write into logs
            echo $this->ExceptionLog($e->getMessage());
            die();
        }
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
     *  Every method which needs to execute a SQL query uses this method
     *
     * @access public
     * @param void
     * @return void
     */
    private function Init($query, $parameters = [])
    {
        # Connect to database
        if ( !$this->isConnected ) {
            $this->connect();
        }
        try {
            // Prepare query
            $this->query = $this->pdo->prepare($query);
            
            // Add parameters to the parameter array 
            $this->bindMore($parameters);
            
            // Bind parameters
            if ( !empty($this->parameters) ) {

                foreach ($this->parameters as $param => $value) {

                    if ( is_int($value[1]) ) {
                        $type = PDO::PARAM_INT;

                    } elseif ( is_bool($value[1]) ) {
                        $type = PDO::PARAM_BOOL;

                    } elseif ( is_null($value[1]) ) {
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
            // Write into log and display Exception
            echo $this->ExceptionLog($e->getMessage(), $query);
            die();
        }
        
        // Reset the parameters
        $this->parameters = [];
    }

    /**
     * Bind params
     *
     * @access public
     * @param array $params
     * @param string $value
     * @return void
     */
    public function bind($params, $value)
    {
        $this->parameters[sizeof($this->parameters)] = [':' . $params , $value];
    }

    /**
     * Bind more params
     *
     * @access public
     * @param array $params
     * @return void
     */
    public function bindMore($params)
    {
        if ( empty($this->parameters) && is_array($params) ) {
            $columns = array_keys($params);
            foreach ($columns as $i => &$column) {
                $this->bind($column, $params[$column]);
            }
        }
    }

    /**
     * Query
     *
     * @access public
     * @param string $query
     * @param array $params null
     * @param const $fetchmode
     * @return void
     */
    public function query($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $query = trim(str_replace("\r", " ", $query));
        $this->Init($query, $params);
        $rawStatement = explode(" ", preg_replace("/\s+|\t+|\n+/", " ", $query));
        
        // Which SQL statement is used 
        $statement = strtolower($rawStatement[0]);
        
        if ( $statement === 'select' || $statement === 'show' ) {
            return $this->query->fetchAll($fetchmode);

        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->query->rowCount();

        } else {
            return null;
        }
    }
    
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Starts the transaction
     * @return boolean, true on success or false on failure
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }
    
    /**
     *  Execute Transaction
     *  @return boolean, true on success or false on failure
     */
    public function executeTransaction()
    {
        return $this->pdo->commit();
    }
    
    /**
     *  Rollback of Transaction
     *  @return boolean, true on success or false on failure
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }
    
    /**
     *  Returns an array which represents a column from the result set 
     *
     *  @param  string $query
     *  @param  array  $params
     *  @return array
     */
    public function column($query, $params = null)
    {
        $this->Init($query, $params);
        $Columns = $this->query->fetchAll(PDO::FETCH_NUM);
        
        $column = null;
        
        foreach ($Columns as $cells) {
            $column[] = $cells[0];
        }
        
        return $column;
        
    }
    /**
     *  Returns an array which represents a row from the result set 
     *
     *  @param  string $query
     *  @param  array  $params
     *      @param  int    $fetchmode
     *  @return array
     */
    public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $this->Init($query, $params);
        $result = $this->query->fetch($fetchmode);
        $this->query->closeCursor(); 
        // Frees up the connection to the server so that other SQL statements may be issued,
        return $result;
    }
    /**
     *  Returns the value of one single field/column
     *
     *  @param  string $query
     *  @param  array  $params
     *  @return string
     */
    public function single($query, $params = null)
    {
        $this->Init($query, $params);
        $result = $this->query->fetchColumn();
        $this->query->closeCursor(); 
        // Frees up the connection to the server so that other SQL statements may be issued
        return $result;
    }
    /** 
     * Writes the log and returns the exception
     *
     * @param  string $message
     * @param  string $sql
     * @return string
     */
    private function ExceptionLog($message, $sql = "")
    {
        $exception = 'Unhandled Exception.';
        
        if (!empty($sql)) {
            # Add the Raw SQL to the Log
            $message .= "\r\nRaw SQL : " . $sql;
        }
        # Write into log
        $this->log->write($message);
        
        // return $exception;
        echo $message;
    }
}
