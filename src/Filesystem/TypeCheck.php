<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.0.0
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT License
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
	public static function isString($data) : bool
	{
		return is_string($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isObject($data) : bool
	{
		return is_object($data);
	}
	
	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isArray($data) : bool
	{
		return is_array($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @param bool $string
	 * @return bool
	 */
	public static function isInt($data, $string = false) : bool
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
	public static function isFloat($data, $string = false) : bool
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
	public static function isBool($data) : bool
	{
		return is_bool($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isNull($data) : bool
	{
		return is_null($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isNan($data) : bool
	{
		return is_nan($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isCallable($data) : bool
	{
		return is_callable($data);
	}

	/**
	 * @access public
	 * @param string $function
	 * @return bool
	 */
	public static function isFunction($function) : bool
	{
		return function_exists($function);
	}

	/**
	 * @access public
	 * @param string $class
	 * @return bool
	 */
	public static function isClass($class) : bool
	{
		return class_exists($class);
	}

	/**
	 * @access public
	 * @param string $sub
	 * @param string $class
	 * @return bool
	 */
	public static function isSubClassOf($sub, $class) : bool
	{
		return is_subclass_of($sub,$class);
	}

	/**
	 * @access public
	 * @param string $class
	 * @param string $interface
	 * @return bool
	 */
	public static function hasInterface($class, $interface) : bool
	{
		$interfaces = class_implements($class);
		return Stringify::contains($interfaces,$interface);
	}

	/**
	 * @access public
	 * @param object $object
	 * @param string $method
	 * @return bool
	 */
	public static function hasMethod($object, $method) : bool
	{
		return method_exists($object,$method);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isCountable($data) : bool
	{
		return is_countable($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isResource($data) : bool
	{
		return is_resource($data);
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @return bool
	 */
	public static function isScalar($data) : bool
	{
		return is_scalar($data);
	}

	/**
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function isStream($path) : bool
	{
	    $scheme = strpos($path,'://');
	    if ( false === $scheme ) {
	        return false;
	    }
	    $stream = substr($path,0,$scheme);
	    return Arrayify::inArray($stream,stream_get_wrappers(),true);
	}
}
