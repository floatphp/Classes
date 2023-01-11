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

final class Arrayify
{
	/**
	 * @access private
	 * @var array|string $orderby
	 */
	private static $orderby;

	/**
	 * @access public
	 * @param mixed $needle
	 * @param array $haystack
	 * @return bool
	 */
	public static function inArray($needle, array $haystack) : bool
	{
		return in_array($needle, $haystack, true);
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
		return array_combine($keys, $values);
	}

	/**
	 * @access public
	 * @param callable $callback
	 * @param array $array
	 * @param array $arrays
	 * @return array
	 */
	public static function map($callback, array $array, array ...$arrays) : array
	{
		return array_map($callback, $array, ...$arrays);
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
		return array_diff($array, ...$arrays);
	}

	/**
	 * @access public
	 * @param string|int $key
	 * @param array $array
	 * @return bool
	 */
	public static function hasKey($key, array $array) : bool
	{
		return array_key_exists($key, $array);
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
	 * @param int $num
	 * @return mixed
	 */
	public static function rand(array $array, $num = 1)
	{
		return array_rand($array, $num);
	}

	/**
	 * @access public
	 * @param array $array
	 * @param int $offset
	 * @param int $length
	 * @param bool $preserve
	 * @return array
	 */
	public static function slice(array $array, $offset, $length = null, $preserve = false) : array
	{
		return array_slice($array, $offset, $length, $preserve);
	}

	/**
	 * @access public
	 * @param array $array
	 * @param callable $callback
	 * @param int $mode
	 * @return array
	 */
	public static function filter(array $array, $callback = null, $mode = 0) : array
	{
		if ( !TypeCheck::isNull($callback) ) {
			return array_filter($array, $callback, $mode);
		}
		return array_filter($array);
	}

	/**
	 * @access public
	 * @param array $array
	 * @param int $case
	 * @return array
	 */
	public static function formatKeyCase($array, $case = CASE_LOWER) : array
	{
		return array_change_key_case((array)$array, $case);
	}

	/**
	 * @access public
	 * @param array|object &$array
	 * @param callable $callback
	 * @param mixed $arg
	 * @return bool
	 */
	public static function walkRecursive(&$array, $callback, $arg = null) : bool
	{
		return array_walk_recursive($array, $callback, $arg);
	}

	/**
	 * @access public
	 * @param array $array
	 * @param int $flags
	 * @return array
	 */
	public static function unique(array $array, $flags = SORT_STRING) : array
	{
		return array_unique($array, $flags);
	}

	/**
	 * @access public
	 * @param array $array
	 * @return array
	 */
	public static function uniqueMultiple(array $array) : array
	{
		return self::map('unserialize', self::unique(
			self::map('serialize', $array)
		));
	}

	/**
	 * @access public
	 * @param array $array
	 * @param string $key
	 * @return array
	 */
	public static function uniqueMultipleByKey(array $array, $key = '') : array
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
     * @param mixed $orderby
     * @param string $order
     * @param bool $preserve, Preserve keys
     * @return array
     */
    public static function sort($array = [], $orderby = [], $order = 'ASC', $preserve = false)
    {
		if ( empty($orderby) ) {
			return $array;
		}

		if ( TypeCheck::isString($orderby) ) {
			$orderby = [$orderby => $order];
		}

		foreach ( $orderby as $field => $direction ) {
			$orderby[$field] = ('DESC' === Stringify::uppercase($direction)) ? 'DESC' : 'ASC';
		}

		static::$orderby = $orderby;

		if ( $preserve ) {
			uasort($array, ['\FloatPHP\Classes\Filesystem\Arrayify', 'sortCallback']);
		} else {
			usort($array, ['\FloatPHP\Classes\Filesystem\Arrayify', 'sortCallback']);
		}

		return $array;
    }

    /**
     * @access public
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    public static function sortCallback($a, $b)
    {
		if ( !static::$orderby ) {
			return 0;
		}

		$a = (array)$a;
		$b = (array)$b;

		foreach ( static::$orderby as $field => $direction ) {

			if ( !isset($a[$field]) || !isset($b[$field]) ) {
				continue;
			}

			if ( $a[$field] == $b[$field] ) {
				continue;
			}

			$results = ('DESC' === $direction) ? [1,-1] : [-1,1];

			if ( TypeCheck::isInt($a[$field],true) && TypeCheck::isInt($b[$field],true) ) {
				return ($a[$field] < $b[$field]) ? $results[0] : $results[1];
			}

			return 0 > strcmp($a[$field],$b[$field]) ? $results[0] : $results[1];
		}

		return 0;
    }
}
