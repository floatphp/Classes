<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.0.0
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2022 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

final class Get
{
	/**
	 * @access public
	 * @param string $item
	 * @return mixed
	 */
	public static function get($item = null)
	{
		if ( $item ) {
			return self::isSetted($item) ? $_GET[$item] : false;
		} else {
			return $_GET;
		}
	}

	/**
	 * @access public
	 * @param string $item
	 * @param mixed $value
	 * @return void
	 */
	public static function set($item, $value = null)
	{
		$_GET[$item] = $value;
	}
	
	/**
	 * @access public
	 * @param string $item
	 * @return bool
	 */
	public static function isSetted($item = null)
	{
		if ( $item ) {
			return isset($_GET[$item]);
		} else {
			return isset($_GET) && !empty($_GET);
		}
	}
}
