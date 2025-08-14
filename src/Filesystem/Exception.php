<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

/**
 * Advanced exception manipulation.
 */
final class Exception extends \Exception
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
	 * @return mixed
	 */
	public static function getLastError() : mixed
	{
		return error_get_last();
	}

	/**
	 * Clear last error.
	 *
	 * @access public
	 * @return void
	 */
	public static function clearLastError() : void
	{
		error_clear_last();
	}

	/**
	 * Trigger user error.
	 *
	 * [E_USER_NOTICE: 1024]
	 *
	 * @access public
	 * @param string $error
	 * @param int $level
	 * @return bool
	 */
	public static function trigger(string $error, int $level = 1024) : bool
	{
		return trigger_error($error, $level);
	}

	/**
	 * Log user error.
	 *
	 * @access public
	 * @param string $error
	 * @param mixed $type
	 * @param mixed $path
	 * @param mixed $headers
	 * @return void
	 */
	public static function log(string $error, mixed $type = 0, mixed $path = null, mixed $headers = null) : void
	{
		$logger = new Logger();
		$logger->log($error, $type, $path, $headers);
	}

	/**
	 * Throw error with exit.
	 * 
	 * @access public
	 * @param string $error
	 * @return void
	 */
	public static function throw(string $error) : never
	{
		exit($error);
	}
}
