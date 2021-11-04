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

final class Arrayify
{
	/**
	 * @access public
	 * @param mixed $needle
	 * @param array $haystack
	 * @param bool $strict
	 * @return bool
	 */
	public static function inArray($needle, array $haystack, bool $strict = false) : bool
	{
		return in_array($needle,$haystack,$strict);
	}

	/**
	 * @access public
	 * @param array $arrays
	 * @return array
	 */
	public static function merge(array ...$arrays) : array
	{
		return array_merge(...$arrays);
	}

	/**
	 * @access public
	 * @param array $array
	 * @param mixed $values
	 * @return int
	 */
	public static function push(array &$array, ...$values) : int
	{
		return array_push($array, ...$values);
	}

	/**
	 * @access public
	 * @param array $keys
	 * @param array $values
	 * @return array
	 */
	public static function combine(array $keys, array $values) : array
	{
		return array_combine($keys,$values);
	}

	/**
	 * @access public
	 * @param mixed $callback
	 * @param array $array
	 * @param array $arrays
	 * @return array
	 */
	public static function map($callback = null, array $array, array ...$arrays) : array
	{
		return array_map($callback,$array,...$arrays);
	}

	/**
	 * @access public
	 * @param array $array
	 * @return mixed
	 */
	public static function shift(array &$array)
	{
		return array_shift($array);
	}
	
	/**
	 * @access public
	 * @param array $array
	 * @return mixed
	 */
	public static function pop(array &$array)
	{
		return array_pop($array);
	}

	/**
	 * @access public
	 * @param array $array
	 * @param array $arrays
	 * @return mixed
	 */
	public static function diff(array $array, array ...$arrays)
	{
		return array_diff($array,...$arrays);
	}

	/**
	 * @access public
	 * @param string|int $key
	 * @param array $array
	 * @return bool
	 */
	public static function hasKey($key, array $array) : bool
	{
		return array_key_exists($key,$array);
	}

	/**
	 * @access public
	 * @param array $array
	 * @return array
	 */
	public static function keys(array $array) : array
	{
		return array_keys($array);
	}

	/**
	 * @access public
	 * @param array $array
	 * @return array
	 */
	public static function values(array $array) : array
	{
		return array_values($array);
	}

	/**
	 * @access public
	 * @param array $array
	 * @param int $flags
	 * @return array
	 */
	public static function unique(array $array, int $flags = SORT_STRING) : array
	{
		return array_unique($array,$flags);
	}

	/**
	 * @access public
	 * @param array $array
	 * @param string $key
	 * @return array
	 */
	public static function uniqueMultiple(array $array, string $key = '') : array
	{
		$temp = [];
		foreach ($array as &$val) {
			if ( !isset($temp[$val[$key]]) ) {
				$temp[$val[$key]] =& $val;
			}
		}
       	$array = self::values($temp);
       	return $array;
	}

	/**
	 * @access public
	 * @param array $array
	 * @param int $num
	 * @return mixed
	 */
	public static function rand(array $array, int $num = 1)
	{
		return array_rand($array,$num);
	}

	/**
	 * @access public
	 * @param array $array
	 * @param int $offset
	 * @param int $length
	 * @param bool $preserve
	 * @return array
	 */
	public static function slice(array $array, int $offset, $length = null, bool $preserve = false) : array
	{
		return array_slice($array,$offset,$length,$preserve);
	}

	/**
	 * @access public
	 * @param array $array
	 * @param callable $callable
	 * @param int $mode
	 * @return array
	 */
	public static function filter(array $array, $callable = null, $mode = null) : array
	{
		if ( $callable ) {
			return array_filter($array,$callable,$mode);
		}
		return array_filter($array);
	}
}
