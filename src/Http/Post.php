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

final class Post
{
	/**
	 * Get _POST value.
	 * 
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public static function get(?string $key = null) : mixed
	{
		if ( $key ) {
			return self::isSetted($key) ? $_POST[$key] : null;
		}
		return self::isSetted() ? $_POST : null;
	}

	/**
	 * Set _POST value.
	 * 
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public static function set(?string $key = null, $value = null) : void
	{
		$_POST[$key] = $value;
	}

	/**
	 * Check _POST value.
	 * 
	 * @access public
	 * @param string $key
	 * @return bool
	 */
	public static function isSetted(?string $key = null) : bool
	{
		if ( $key ) {
			return isset($_POST[$key]);
		}
		return isset($_POST) && !empty($_POST);
	}

	/**
	 * Unset _POST value.
	 * 
	 * @access public
	 * @param string $key
	 * @return void
	 */
	public static function unset(?string $key = null) : void
	{
		if ( $key ) {
			unset($_POST[$key]);

		} else {
			$_POST = [];
		}
	}
}
