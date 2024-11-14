<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.3.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

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
		return ($value === false);
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
		return ($value === true);
	}

	/**
	 * Check empty (string or array).
	 *
	 * @access public
	 * @param mixed $value
	 * @return bool
	 */
	public static function isEmpty($value) : bool
	{
		if ( self::isString($value) ) {
			return (trim($value) === '');
		}
		if ( self::isArray($value) ) {
			return empty($value);
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
		$implements = class_implements($class);
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
	 * Check ressource.
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
	 * Check stream.
	 *
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
		return Arrayify::inArray($stream, stream_get_wrappers());
	}

	/**
	 * Check dynamic type.
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
