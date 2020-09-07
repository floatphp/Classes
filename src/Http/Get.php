<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Http Component
 * @version   : 1.1.0
 * @category  : PHP framework
 * @copyright : (c) JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace floatPHP\Classes\Http;

class Get
{
	/**
	 * @access public
	 * @param string $item
	 * @return mixed
	 */
	public static function get($item = null)
	{
		if ( isset($item) ) {
			return $_GET[$item];
		} else return $_GET;
	}

	/**
	 * @access public
	 * @param string $item
	 * @param mixed $value
	 * @return void
	 */
	public static function set($item,$value)
	{
		$_GET[$item] = $value;
	}
	
	/**
	 * @access public
	 * @param string $item
	 * @return boolean
	 */
	public static function isSetted($item = null)
	{
		if ( $item && isset($_GET[$item]) ) {
			return true;
		} elseif ( !$item && isset($_GET) ) {
			return true;
		} else return false;
	}
}
