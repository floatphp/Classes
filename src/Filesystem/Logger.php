<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Filesystem Component
 * @version   : 0.1
 * @category  : PHP framework
 * @copyright : (c) JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com/
 * @license   : non-open-source
 */

namespace FloatPHP\Classes\Filesystem;

final class Logger
{
    /**
     * @access private
     */
    private $extension = 'log';
    private $filename = 'debug';
    private $path = '/';

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
     * @param string $filename
     * @return void
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
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
     * @param string $content
     * @return void
     */
    public function debug($content)
    {
        $this->write('DEBUG',$content);
    }

    /**
     * @access public
     * @param string $content
     * @return void
     */
    public function error($content)
    {
        $this->write('ERROR',$content);
    }

    /**
     * @access public
     * @param string $content
     * @return void
     */
    public function warning($content)
    {
        $this->write('WARNING',$content);
    }

    /**
     * @access public
     * @param string $content
     * @return void
     */
    public function info($content)
    {
        $this->write('INFO',$content);
    }

    /**
     * @access public
     * @param void
     * @return array
     */
    public function getTypes()
    {
        return ['debug','error','warning','info'];
    }

    /**
     * Log natif errors
     *
     * @access public
     * @param string $content
     * @param int $type 0
     * @param string $path
     * @param string $headers
     * @return void
     */
    public function log($content = '', $type = 0, $path = null, $headers = null)
    {
        error_log($content,$type,$path,$headers);
    }

    /**
     * @access private
     * @param string $status 
     * @param string $content 
     * @return void
     */
    private function write($status, $content)
    {
        $date = date('[d-m-Y]');
        $log  = "{$this->path}/{$this->filename}-{$date}.{$this->extension}";
        $date = date('[d-m-Y H:i:s]');
        $msg  = "{$date} : [{$status}] - {$content}" . PHP_EOL;
        File::w($log,$msg,true);
    }
}
