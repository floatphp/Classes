<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.0.1
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2023 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

final class TypeCheck
{
	/**
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isString($value) : bool
	{
		return is_string($value);
	}

	/**
	 * @access public
	 * @param mixed $value
	 * @param string $class
	 * @param bool $string, Allow string
	 * @return bool
	 */
	public static function isObject($value, $class = null, bool $string = false) : bool
	{
		if ( $class ) {
			return is_a($value, $class, $string);
		}
		return is_object($value);
	}
	
	/**
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isArray($value) : bool
	{
		return is_array($value);
	}

	/**
	 * @access public
	 * @param mixed $value
	 * @param bool $string
	 * @return bool
	 */
	public static function isInt($value, bool $string = false) : bool
	{
		if ( $string ) {
			return is_numeric($value);
		}
		return is_int($value);
	}

	/**
	 * @access public
	 * @param mixed $value
	 * @param bool $string
	 * @return bool
	 */
	public static function isFloat($value, bool $string = false) : bool
	{
		if ( $string ) {
			$value = (float)$value;
		}
		return is_float($value);
	}

	/**
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isBool($value) : bool
	{
		return is_bool($value);
	}

	/**
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isNull($value) : bool
	{
		return is_null($value);
	}

	/**
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isNan($value) : bool
	{
		return is_nan($value);
	}

	/**
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isCallable($value) : bool
	{
		return is_callable($value);
	}

	/**
	 * @access public
	 * @param string $function
	 * @return bool
	 */
	public static function isFunction(string $function) : bool
	{
		return function_exists($function);
	}

	/**
	 * @access public
	 * @param string $class
	 * @return bool
	 */
	public static function isClass(string $class) : bool
	{
		return class_exists($class);
	}

	/**
	 * @access public
	 * @param string $sub
	 * @param string $class
	 * @return bool
	 */
	public static function isSubClassOf(string $sub, string $class) : bool
	{
		return is_subclass_of($sub, $class);
	}

	/**
	 * @access public
	 * @param string $class
	 * @param string $interface
	 * @return bool
	 */
	public static function hasInterface(string $class, string $interface) : bool
	{
		$interfaces = class_implements($class);
		return Stringify::contains($interfaces, $interface);
	}

	/**
	 * @access public
	 * @param object $object
	 * @param string $method
	 * @return bool
	 */
	public static function hasMethod(object $object, string $method) : bool
	{
		return method_exists($object, $method);
	}

	/**
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isCountable($value) : bool
	{
		return is_countable($value);
	}

	/**
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isResource($value) : bool
	{
		return is_resource($value);
	}

	/**
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isScalar($value) : bool
	{
		return is_scalar($value);
	}

	/**
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function isStream(string $path) : bool
	{
	    $scheme = strpos($path, '://');
	    if ( false === $scheme ) {
	        return false;
	    }
	    $stream = substr($path, 0, $scheme);
	    return Arrayify::inArray($stream, stream_get_wrappers(), true);
	}
}
