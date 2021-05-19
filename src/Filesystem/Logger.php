<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Filesystem Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Filesystem;

use FloatPHP\Interfaces\Classes\LoggerInterface;

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
     * @param string $path
     * @param string $filename
     * @param string $extension
     */
    public function __construct($path = '/', $filename = 'debug', $extension = 'log')
    {
        $this->path = $path;
        $this->filename = $filename;
        $this->extension = $extension;
    }

    /**
     * @access public
     * @param string $path
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @access public
     * @param string $filename
     * @return void
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @access public
     * @param string $extension
     * @return void
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * @access public
     * @param string $message
     * @return void
     */
    public function debug($message)
    {
        $this->write('debug',$message);
    }

    /**
     * @access public
     * @param string $message
     * @return void
     */
    public function error($message)
    {
        $this->write('error',$message);
    }

    /**
     * @access public
     * @param string $message
     * @return void
     */
    public function warning($message)
    {
        $this->write('warning',$message);
    }

    /**
     * @access public
     * @param string $message
     * @return void
     */
    public function info($message)
    {
        $this->write('info',$message);
    }

    /**
     * @access public
     * @param string $message
     * @param string $type
     * @return void
     */
    public function custom($message, $type = 'custom')
    {
        $this->write($type,$message);
    }

    /**
     * Log natif errors
     *
     * @access public
     * @param string $message
     * @param int $type 0
     * @param string $path
     * @param string $headers
     * @return void
     */
    public function log($message = '', $type = 0, $path = null, $headers = null)
    {
        error_log($message,$type,$path,$headers);
    }

    /**
     * @access protected
     * @param string $status 
     * @param string $message 
     * @return void
     */
    protected function write($status, $message)
    {
        $date = date('[d-m-Y]');
        $log  = "{$this->path}/{$this->filename}-{$date}.{$this->extension}";
        $date = date('[d-m-Y H:i:s]');
        $msg  = "{$date} : [{$status}] - {$message}" . PHP_EOL;
        File::w($log,$msg,true);
    }
}
