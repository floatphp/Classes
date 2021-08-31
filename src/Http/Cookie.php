<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Http Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Http;

final class Cookie
{
	/**
	 * @access public
	 * @param string $item
	 * @return mixed
	 */
	public static function get($item = null)
	{
		if ( $item ) {
			return self::isSetted($item) ? $_COOKIE[$item] : false;
		} else {
			return $_COOKIE;
		}
	}

	/**
	 * @access public
	 * @param string $item
	 * @param mixed $value
	 * @param array $options
	 * @return bool
	 */
	public static function set($item, $value = null, $options = [])
	{
		return setcookie($item,$value,$options);
	}
	
	/**
	 * @access public
	 * @param string $item
	 * @return bool
	 */
	public static function isSetted($item = null)
	{
		if ( $item ) {
			return isset($_COOKIE[$item]);
		} else {
			return isset($_COOKIE);
		}
	}
}
