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

class Server
{
	/**
	 * @access public
	 * @param string $item
	 * @return mixed
	 */
	public static function get($item = null)
	{
		if ( isset($item) ) {
			return $_SERVER[$item];
		} else return $_SERVER;
	}

	/**
	 * @access public
	 * @param void
	 * @return string
	 */
	public static function getRemote()
	{
		return self::isSetted('REMOTE_ADDR') 
		? self::get('REMOTE_ADDR') 
		: self::get('HTTP_X_FORWARDED_FOR');
	}

	/**
	 * @access public
	 * @param string $item
	 * @param mixed $value
	 * @return void
	 */
	public static function set($item,$value)
	{
		$_SERVER[$item] = $value;
	}

	/**
	 * @access public
	 * @param string $item
	 * @return boolean
	 */
	public static function isSetted($item = null)
	{
		if ( $item && isset($_SERVER[$item]) ) return true;
		elseif ( !$item && isset($_SERVER) ) return true;
		else return false;
	}

	/**
	 * @access public
	 * @param void
	 * @return boolean
	 */
	public static function isAuth()
	{
		if ( isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) ) {
			return true;
		}
		return false;
	}

	/**
	 * @access public
	 * @param void
	 * @return boolean
	 */
	public static function isHttps()
	{
		if ( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ) {
		    return true;
		}
		return false;
	}
}
