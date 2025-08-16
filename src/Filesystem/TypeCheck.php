<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file is a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

/**
 * Advanced types checker.
 */
final class TypeCheck
{
	/**
	 * Check string.
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isString($value) : bool
	{
		return is_string($value);
	}

	/**
	 * Check object.
	 *
	 * @access public
	 * @param mixed $value
	 * @param string $class
	 * @param bool $string, Allow string
	 * @return bool
	 */
	public static function isObject($value, ?string $class = null, bool $string = false) : bool
	{
		if ( $class ) {
			// Validate class name input
			if ( trim($class) === '' ) {
				return false;
			}
			return is_a($value, $class, $string);
		}
		return is_object($value);
	}

	/**
	 * Check array.
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isArray($value) : bool
	{
		return is_array($value);
	}

	/**
	 * Check iterator.
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isIterator($value) : bool
	{
		return is_iterable($value);
	}

	/**
	 * Check int.
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isInt($value) : bool
	{
		return is_int($value);
	}

	/**
	 * Check numeric (string cast).
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isNumeric($value) : bool
	{
		return is_numeric($value);
	}

	/**
	 * Check zero int.
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isZero($value) : bool
	{
		return $value === 0;
	}

	/**
	 * Check negative number.
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isNegative($value) : bool
	{
		// Handle edge cases: NaN, infinite values
		if ( self::isNan($value) || !is_finite($value) ) {
			return false;
		}
		return (self::isFloat($value) || self::isInt($value))
			&& $value < 0;
	}

	/**
	 * Check float.
	 *
	 * @access public
	 * @param mixed $value
	 * @param bool $string
	 * @return bool
	 */
	public static function isFloat($value, bool $string = false) : bool
	{
		if ( $string ) {
			// Validate string input before casting
			if ( !self::isString($value) ) {
				return false;
			}
			// Handle edge cases for string-to-float conversion
			if ( trim($value) === '' ) {
				return false;
			}
			$value = (float)$value;
		}
		return is_float($value);
	}

	/**
	 * Check bool.
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isBool($value) : bool
	{
		return is_bool($value);
	}

	/**
	 * Check null.
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isNull($value) : bool
	{
		return is_null($value);
	}

	/**
	 * Check false.
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isFalse($value) : bool
	{
		return $value === false;
	}

	/**
	 * Check true.
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isTrue($value) : bool
	{
		return $value === true;
	}

	/**
	 * Check empty (string or array or null).
	 *
	 * @access public
	 * @param mixed $value
	 * @param bool $null
	 * @return bool
	 */
	public static function isEmpty($value, bool $null = false) : bool
	{
		if ( self::isString($value) ) {
			return trim($value) === '';
		}
		if ( self::isArray($value) ) {
			return empty($value);
		}
		if ( $null ) {
			return self::isNull($value);
		}
		return false;
	}

	/**
	 * Check NAN (Not a number).
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isNan($value) : bool
	{
		return is_nan($value);
	}

	/**
	 * Check callable.
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isCallable($value) : bool
	{
		return is_callable($value);
	}

	/**
	 * Check function.
	 *
	 * @access public
	 * @param string $name
	 * @return bool
	 */
	public static function isFunction(string $name) : bool
	{
		// Validate function name input
		if ( trim($name) === '' ) {
			return false;
		}
		return function_exists(Stringify::undash($name));
	}

	/**
	 * Check class.
	 *
	 * @access public
	 * @param string $class
	 * @param bool $autoload
	 * @return bool
	 */
	public static function isClass(string $class, bool $autoload = true) : bool
	{
		// Validate class name input
		if ( trim($class) === '' ) {
			return false;
		}
		return class_exists($class, $autoload);
	}

	/**
	 * Check sub class.
	 *
	 * @access public
	 * @param mixed $sub
	 * @param string $class
	 * @return bool
	 */
	public static function isSubClassOf($sub, string $class) : bool
	{
		return is_subclass_of($sub, $class);
	}

	/**
	 * Check interface.
	 *
	 * @access public
	 * @param string $interface
	 * @param bool $autoload
	 * @return bool
	 */
	public static function isInterface(string $interface, bool $autoload = true) : bool
	{
		// Validate interface name input
		if ( trim($interface) === '' ) {
			return false;
		}
		return interface_exists($interface, $autoload);
	}

	/**
	 * Check interface.
	 *
	 * @access public
	 * @param mixed $class
	 * @param string $interface
	 * @param bool $short
	 * @return bool
	 */
	public static function hasInterface($class, string $interface, bool $short = true) : bool
	{
		// Validate inputs
		if ( trim($interface) === '' ) {
			return false;
		}

		// Handle edge case: if class is not valid
		$implements = class_implements($class);
		if ( !$implements ) {
			return false;
		}

		if ( $short ) {
			foreach ($implements as $key => $value) {
				$implements[$key] = Stringify::basename($value);
			}
		}

		return Arrayify::inArray($interface, (array)$implements);
	}

	/**
	 * Check method.
	 *
	 * @access public
	 * @param mixed $object
	 * @param string $method
	 * @return bool
	 */
	public static function hasMethod($object, string $method) : bool
	{
		// Validate method name input
		if ( trim($method) === '' ) {
			return false;
		}
		return method_exists($object, $method);
	}

	/**
	 * Check countable.
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isCountable($value) : bool
	{
		return is_countable($value);
	}

	/**
	 * Check resource.
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isResource($value) : bool
	{
		return is_resource($value);
	}

	/**
	 * Check scalar.
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isScalar($value) : bool
	{
		return is_scalar($value);
	}

	/**
	 * Check dynamic type (Javascript).
	 *
	 * @access public
	 * @param string $type
	 * @param mixed $value
	 * @return mixed
	 * @internal
	 */
	public static function isDynamicType(string $type, $value = null) : mixed
	{
		if ( !self::isString($value) ) {
			return false;
		}

		$pattern = sprintf('/^%s\|(.+)$/', $type);

		if ( Stringify::match($pattern, $value, $matches) ) {
			$matches = $matches[1] ?? 'NaN';
			if ( $type == 'bool' && $matches == '0' ) {
				$matches = 'NaN';
			}
			return $matches;
		}

		return false;
	}
}
