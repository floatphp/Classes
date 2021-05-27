<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes System Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\System;

use FloatPHP\Classes\Filesystem\Logger;
use \Exception as MainException;

class Exception extends MainException
{
	/**
	 * Handle shutdown exception
	 *
	 * @access protected
	 * @var array $callable
	 * @return void
	 */
	public function shutdown($callable)
	{
		register_shutdown_function($callable);
	}

	/**
	 * Get last error
	 *
	 * @access public
	 * @var void
	 * @return string
	 */
	public function getLastError()
	{
		return error_get_last();
	}

	/**
	 * Clear last error
	 *
	 * @access public
	 * @var void
	 * @return void
	 */
	public function clearLastError()
	{
		error_clear_last();
	}

	/**
	 * Trigger user error
	 *
	 * @access public
	 * @var string $message
	 * @var int $type
	 * @return bool
	 */
	public function trigger($message, $type = E_USER_NOTICE)
	{
		return trigger_error($message,$type);
	}

    /**
	 * Log user error
	 *
     * @access public
     * @param string $message
     * @param string $type
     * @param string $path
     * @param array $headers
     * @return string
     */
    public function log($message = '', $type = 0, $path = null, $headers = null)
    {
    	$logger = new Logger();
        $logger->log($message,$type,$path,$headers);
        return $message;
    }
}
