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
 * Advanced exception and error handling utilities.
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
		if ( !TypeCheck::isCallable($callback) ) {
			return false;
		}
		register_shutdown_function($callback, $args);
		return true;
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
		if ( empty($error) ) {
			return false;
		}
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
		if ( empty($error) ) {
			return;
		}

		try {
			$logger = new Logger();
			$logger->log($error, $type, $path, $headers);

		} catch (\Throwable $e) {
			exit($error);
		}
	}

	/**
	 * Throw error with exit.
	 * 
	 * @access public
	 * @param string $error
	 * @param int $code
	 * @param bool $useExit
	 * @return void
	 */
	public static function throw(string $error, int $code = 0, bool $useExit = false) : never
	{
		if ( $useExit ) {
			exit($error);
		}
		throw new \RuntimeException($error, $code);
	}

	/**
	 * Check if error occurred.
	 *
	 * @access public
	 * @return bool
	 */
	public static function hasError() : bool
	{
		return self::getLastError() !== null;
	}

	/**
	 * Get formatted error message.
	 *
	 * @access public
	 * @param mixed $error
	 * @return string
	 */
	public static function formatError(mixed $error = null) : string
	{
		if ( $error === null ) {
			$error = self::getLastError();
		}

		if ( !is_array($error) ) {
			return (string)$error;
		}

		$message = $error['message'] ?? 'Unknown error';
		$file = $error['file'] ?? 'Unknown file';
		$line = $error['line'] ?? 'Unknown line';
		$type = $error['type'] ?? E_ERROR;

		$errorTypes = [
			E_ERROR             => 'Fatal Error',
			E_WARNING           => 'Warning',
			E_PARSE             => 'Parse Error',
			E_NOTICE            => 'Notice',
			E_CORE_ERROR        => 'Core Error',
			E_CORE_WARNING      => 'Core Warning',
			E_COMPILE_ERROR     => 'Compile Error',
			E_COMPILE_WARNING   => 'Compile Warning',
			E_USER_ERROR        => 'User Error',
			E_USER_WARNING      => 'User Warning',
			E_USER_NOTICE       => 'User Notice',
			E_STRICT            => 'Strict Standards',
			E_RECOVERABLE_ERROR => 'Recoverable Error',
			E_DEPRECATED        => 'Deprecated',
			E_USER_DEPRECATED   => 'User Deprecated'
		];

		$typeName = $errorTypes[$type] ?? 'Unknown Error';

		return "[{$typeName}] {$message} in {$file} on line {$line}";
	}

	/**
	 * Set custom error handler.
	 *
	 * @access public
	 * @param callable $handler
	 * @param int $errorTypes
	 * @return callable|null
	 */
	public static function setHandler(callable $handler, int $errorTypes = E_ALL) : ?callable
	{
		if ( !TypeCheck::isCallable($handler) ) {
			return null;
		}
		return set_error_handler($handler, $errorTypes);
	}

	/**
	 * Restore previous error handler.
	 *
	 * @access public
	 * @return bool
	 */
	public static function restoreHandler() : bool
	{
		return restore_error_handler();
	}
}
