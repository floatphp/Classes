<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

/**
 * Advanced HTTP REQUEST manipulation.
 */
final class Request
{
	/**
	 * Get _REQUEST value.
	 *
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public static function get(?string $key = null) : mixed
	{
		if ( $key ) {
			return self::isSetted($key) ? $_REQUEST[$key] : null;
		}
		return self::isSetted() ? $_REQUEST : null;
	}

	/**
	 * Set _REQUEST value.
	 * 
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public static function set(string $key, $value = null) : void
	{
		$_REQUEST[$key] = $value;
	}

	/**
	 * Check _REQUEST value.
	 * 
	 * @access public
	 * @param string $key
	 * @return bool
	 */
	public static function isSetted(?string $key = null) : bool
	{
		if ( $key ) {
			return isset($_REQUEST[$key]);
		}
		return isset($_REQUEST) && !empty($_REQUEST);
	}

	/**
	 * Unset _REQUEST value.
	 * 
	 * @access public
	 * @param string $key
	 * @return void
	 */
	public static function unset(?string $key = null) : void
	{
		if ( $key ) {
			unset($_REQUEST[$key]);

		} else {
			$_REQUEST = [];
		}
	}
}
