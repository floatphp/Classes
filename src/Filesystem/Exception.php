<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.1.0
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

final class Exception
{
	/**
	 * Handle shutdown exception.
	 *
	 * @access public
	 * @param callable $callback
	 * @param array $args
	 * @return bool
	 */
	public static function handle(callable $callback, ?array $args = null) : bool
	{
		return (bool)register_shutdown_function($callback, $args);
	}

	/**
	 * Get last error.
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	public static function getLastError()
	{
		return error_get_last();
	}

	/**
	 * Clear last error.
	 *
	 * @access public
	 * @param void
	 * @return void
	 */
	public static function clearLastError()
	{
		error_clear_last();
	}

	/**
	 * Trigger user error.
	 *
	 * @access public
	 * @param string $error
	 * @param int $type
	 * @return bool
	 */
	public static function trigger(string $error, int $type = E_USER_NOTICE) : bool
	{
		return trigger_error($error, $type);
	}
	
    /**
	 * Log user error.
	 *
     * @access public
     * @param string $error
     * @param string $type
     * @param string $path
     * @param array $headers
     * @return void
     */
    public static function log(string $error, $type = 0, $path = null, $headers = null)
    {
    	$logger = new Logger();
        $logger->log($error, $type, $path, $headers);
    }

	/**
	 * Throw error with die.
	 * 
	 * @access public
	 * @param string $error
	 * @return void
	 */
	public static function throw(string $error)
	{
		die($error);
	}
}
