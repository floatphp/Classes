<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.3.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
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
			return self::isSetted($key) ? $_GET[$key] : null;
		}
		return self::isSetted() ? $_GET : null;
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
	public static function isSetted(?string $key = null) : bool
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
