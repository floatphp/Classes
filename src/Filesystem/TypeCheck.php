<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Filesystem Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Filesystem;

final class TypeCheck
{
	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isString($data)
	{
		return is_string($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isObject($data)
	{
		return is_object($data);
	}
	
	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isArray($data)
	{
		return is_array($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @param bool $string
	 * @return bool
	 */
	public static function isInt($data, $string = false)
	{
		if ( $string ) {
			return is_numeric($data);
		}
		return is_int($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @param bool $string
	 * @return bool
	 */
	public static function isFloat($data, $string = false)
	{
		if ( $string ) {
			$data = (float)$data;
		}
		return is_float($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isBool($data)
	{
		return is_bool($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isNull($data)
	{
		return is_null($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isNan($data)
	{
		return is_nan($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isCallable($data)
	{
		return is_callable($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isCountable($data)
	{
		return is_countable($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isResource($data)
	{
		return is_resource($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isScalar($data)
	{
		return is_scalar($data);
	}

	/**
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function isStream($path)
	{
	    $scheme = strpos($path,'://');
	    if ( false === $scheme ) {
	        return false;
	    }
	    $stream = substr($path,0,$scheme);
	    return in_array($stream,stream_get_wrappers(),true);
	}
}
