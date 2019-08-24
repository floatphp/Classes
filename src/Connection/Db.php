<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Connection Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 */

namespace floatPHP\Classes\Connection;

use floatPHP\Classes\Filesystem\Logger;
use \PDOException;
use \PDO;

class Db
{

    private $pdo;

    private $query;

    private $settings;

    private $isConnected = false;

    private $log;

    private $parameters;
    
    public function __construct()
    {
        $this->log = new Logger();
        $this->connect();
        $this->parameters = [];
    }
    
    private function connect()
    {
        $this->settings = parse_ini_file(dirname(dirname(dirname(__FILE__))) . "/secret.ini");

        $dsn = 'mysql:dbname=' . $this->settings["db"] . ';host=' . $this->settings["host"] . ';port=' . $this->settings["port"] . '';
        try {

            # Read settings from INI file, set UTF8
            $this->pdo = new PDO(
                $dsn,
                $this->settings["user"],
                $this->settings["pswd"],
                [PDO::MYSQL_ATTR_INIT_COMMAND =>'SET NAMES utf8']
            );

            # We can now log any exceptions on Fatal error. 
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            # Disable emulation of prepared statements, use REAL prepared statements instead.
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            # Connection succeeded, set the boolean to true.
            $this->isConnected = true;
        }
        catch (PDOException $e) {
            # Write into log
            echo $this->ExceptionLog($e->getMessage());
            die();
        }
    }
    /**
    * Close connection
    *
    * force ending connection
    * @param void
    * @return void
    */
    public function close()
    {
        $this->pdo = null;
    }
    
    /**
     *	Every method which needs to execute a SQL query uses this method.
     *	
     *	1. If not connected, connect to the database.
     *	2. Prepare Query.
     *	3. Parameterize Query.
     *	4. Execute Query.	
     *	5. On exception : Write Exception into the log + SQL query.
     *	6. Reset the Parameters.
     */
    private function Init($query, $parameters = "")
    {
        # Connect to database
        if (!$this->isConnected) {
            $this->connect();
        }
        try {
            # Prepare query
            $this->query = $this->pdo->prepare($query);
            
            # Add parameters to the parameter array	
            $this->bindMore($parameters);
            
            # Bind parameters
            if (!empty($this->parameters)) {

                foreach ($this->parameters as $param => $value) {

                    if(is_int($value[1])) {
                        $type = PDO::PARAM_INT;
                    } else if(is_bool($value[1])) {
                        $type = PDO::PARAM_BOOL;
                    } else if(is_null($value[1])) {
                        $type = PDO::PARAM_NULL;
                    } else {
                        $type = PDO::PARAM_STR;
                    }
                    // Add type when binding the values to the column
                    $this->query->bindValue($value[0], $value[1], $type);
                }
            }
            
            # Execute SQL 
            $this->query->execute();
        }
        catch (PDOException $e) {
            # Write into log and display Exception
            echo $this->ExceptionLog($e->getMessage(), $query);
            die();
        }
        
        # Reset the parameters
        $this->parameters = [];
    }

    public function bind($para, $value)
    {
        $this->parameters[sizeof($this->parameters)] = [":" . $para , $value];
    }

    public function bindMore($parray)
    {
        if (empty($this->parameters) && is_array($parray)) {
            $columns = array_keys($parray);
            foreach ($columns as $i => &$column) {
                $this->bind($column, $parray[$column]);
            }
        }
    }

    public function query($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $query = trim(str_replace("\r", " ", $query));
        
        $this->Init($query, $params);
        
        $rawStatement = explode(" ", preg_replace("/\s+|\t+|\n+/", " ", $query));
        
        # Which SQL statement is used 
        $statement = strtolower($rawStatement[0]);
        
        if ($statement === 'select' || $statement === 'show') {
            return $this->query->fetchAll($fetchmode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->query->rowCount();
        } else {
            return NULL;
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
     *	Returns an array which represents a column from the result set 
     *
     *	@param  string $query
     *	@param  array  $params
     *	@return array
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
     *	Returns an array which represents a row from the result set 
     *
     *	@param  string $query
     *	@param  array  $params
     *   	@param  int    $fetchmode
     *	@return array
     */
    public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $this->Init($query, $params);
        $result = $this->query->fetch($fetchmode);
        $this->query->closeCursor(); // Frees up the connection to the server so that other SQL statements may be issued,
        return $result;
    }
    /**
     *	Returns the value of one single field/column
     *
     *	@param  string $query
     *	@param  array  $params
     *	@return string
     */
    public function single($query, $params = null)
    {
        $this->Init($query, $params);
        $result = $this->query->fetchColumn();
        $this->query->closeCursor(); // Frees up the connection to the server so that other SQL statements may be issued
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
