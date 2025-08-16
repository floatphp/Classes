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
 * This file is a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

/**
 * Advanced HTTP GET manipulation.
 */
final class Get
{
	/**
	 * Get _GET value.
	 *
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public static function get(?string $key = null) : mixed
	{
		if ( $key ) {
			return self::isSet($key) ? $_GET[$key] : null;
		}
		return self::isSet() ? $_GET : null;
	}

	/**
	 * Set _GET value.
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public static function set(string $key, $value = null) : void
	{
		$_GET[$key] = $value;
	}

	/**
	 * Check _GET value.
	 *
	 * @access public
	 * @param string $key
	 * @return bool
	 */
	public static function isSet(?string $key = null) : bool
	{
		if ( $key ) {
			return isset($_GET[$key]);
		}
		return isset($_GET) && !empty($_GET);
	}

	/**
	 * Unset _GET value.
	 *
	 * @access public
	 * @param string $key
	 * @return void
	 */
	public static function unset(?string $key = null) : void
	{
		if ( $key ) {
			unset($_GET[$key]);

		} else {
			$_GET = [];
		}
	}
}
