<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.3.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

use FloatPHP\Interfaces\Classes\LoggerInterface;

/**
 * Built-in logger class.
 */
class Logger implements LoggerInterface
{
    /**
     * @access protected
     * @var string $path
     * @var string $filename
     * @var string $extension
     */
    protected $path;
    protected $filename;
    protected $extension;

    /**
     * Init logger.
     * 
     * @param string $path
     * @param string $filename
     * @param string $extension
     */
    public function __construct(?string $path = '/', string $file = 'debug', string $ext = 'log')
    {
        $this->setPath($path);
        $this->setFilename($file);
        $this->setExtension($ext);
    }

    /**
     * Set log path.
     *
     * @access public
     * @param string $path
     * @return object
     */
    public function setPath(string $path) : self
    {
        $this->path = $path;
        if ( !File::isDir($this->path) ) {
            File::addDir($this->path);
        }
        return $this;
    }

    /**
     * Get logger path.
     * 
     * @access public
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * Set log filename.
     *
     * @access public
     * @param string $filename
     * @return object
     */
    public function setFilename(string $filename) : self
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Set log extension.
     *
     * @access public
     * @param string $extension
     * @return object
     */
    public function setExtension(string $extension) : self
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * Log debug message.
     *
     * @access public
     * @param mixed $message
     * @param bool $isArray
     * @return bool
     */
    public function debug($message, bool $isArray = false) : bool
    {
        if ( $isArray ) {
            $message = print_r($message, true);
        }
        return $this->write('debug', $message);
    }

    /**
     * Log error message.
     *
     * @access public
     * @param string $message
     * @return bool
     */
    public function error(string $message) : bool
    {
        return $this->write('error', $message);
    }

    /**
     * Log warning message.
     *
     * @access public
     * @param string $message
     * @return bool
     */
    public function warning(string $message) : bool
    {
        return $this->write('warning', $message);
    }

    /**
     * Log info message.
     *
     * @access public
     * @param string $message
     * @return bool
     */
    public function info(string $message) : bool
    {
        return $this->write('info', $message);
    }

    /**
     * Log custom message.
     *
     * @access public
     * @param string $message
     * @param string $type
     * @return bool
     */
    public function custom(string $message, string $type = 'custom') : bool
    {
        return $this->write($type, $message);
    }

    /**
     * Log natif error.
     *
     * @access public
     * @param string $message
     * @param int $type 0
     * @param string $path
     * @param string $headers
     * @return bool
     */
    public function log(string $message, int $type = 0, ?string $path = null, ?string $headers = null) : bool
    {
        return error_log($message, $type, $path, $headers);
    }

    /**
     * Write message.
     *
     * @access protected
     * @param string $status 
     * @param string $message 
     * @return bool
     */
    protected function write(string $status, string $message) : bool
    {
        $date = date('[d-m-Y]');
        $log = "{$this->path}/{$this->filename}-{$date}.{$this->extension}";
        $date = date('[d-m-Y H:i:s]');
        $msg = "{$date} : [{$status}] - {$message}" . Stringify::break();
        return File::w($log, $msg, true);
    }
}
