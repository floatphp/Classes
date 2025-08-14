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
 * Advanced array manipulation.
 */
final class Arrayify
{
	/**
	 * Check array value.
	 *
	 * @access public
	 * @param mixed $value
	 * @param array $array
	 * @return bool
	 */
	public static function inArray($value, array $array) : bool
	{
		return in_array($value, $array, strict: true);
	}

	/**
	 * Search array key.
	 *
	 * @access public
	 * @param mixed $value
	 * @param array $array
	 * @return mixed
	 */
	public static function search($value, array $array) : mixed
	{
		return array_search($value, $array, strict: true);
	}

	/**
	 * Merge arrays.
	 *
	 * @access public
	 * @param array $arrays
	 * @return array
	 */
	public static function merge(array ...$arrays) : array
	{
		return array_merge(...$arrays);
	}

	/**
	 * Merge multidimensional arrays.
	 *
	 * @access public
	 * @param array $default
	 * @param array $target
	 * @return array
	 */
	public static function mergeAll(array $default, array $target, bool $strict = false) : array
	{
		foreach ($default as $key => $value) {

			if ( TypeCheck::isArray($value) ) {
				$temp = $target[$key] ?? [];
				$target[$key] = self::mergeAll($value, $temp, $strict);

			} else {
				if ( !isset($target[$key]) ) {
					$target[$key] = $value;

				} else {
					if ( $strict && TypeCheck::isEmpty($target[$key]) ) {
						$target[$key] = $value;
					}
				}
			}
		}

		return $target;
	}

	/**
	 * Push array.
	 *
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
	 * Combine array.
	 *
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
	 * Map array.
	 *
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
	 * Shift array.
	 *
	 * @access public
	 * @param array $array
	 * @return mixed
	 */
	public static function shift(array &$array) : mixed
	{
		return array_shift($array);
	}

	/**
	 * Pop array.
	 *
	 * @access public
	 * @param array $array
	 * @return mixed
	 */
	public static function pop(array &$array) : mixed
	{
		return array_pop($array);
	}

	/**
	 * Get array diff.
	 *
	 * @access public
	 * @param array $array
	 * @param array $arrays
	 * @return array
	 */
	public static function diff(array $array, array ...$arrays) : array
	{
		return array_diff($array, ...$arrays);
	}

	/**
	 * Check array key.
	 *
	 * @access public
	 * @param mixed $key
	 * @param array $array
	 * @return bool
	 */
	public static function hasKey($key, array $array) : bool
	{
		return array_key_exists($key, $array);
	}

	/**
	 * Get array keys.
	 *
	 * @access public
	 * @param array $array
	 * @param mixed $value
	 * @param bool $search
	 * @return array
	 */
	public static function keys(array $array, $value = null, bool $search = false) : array
	{
		if ( $search ) {
			return array_keys($array, $value, true);
		}
		return array_keys($array);
	}

	/**
	 * Get array first key.
	 *
	 * @access public
	 * @param array $array
	 * @return mixed
	 */
	public static function key(array $array) : mixed
	{
		return array_key_first($array);
	}

	/**
	 * Fill array keys.
	 *
	 * @access public
	 * @param array $array
	 * @param mixed $values
	 * @return array
	 */
	public static function fillKeys(array $array, $values) : array
	{
		return array_fill_keys($array, $values);
	}

	/**
	 * Get array values.
	 *
	 * @access public
	 * @param array $array
	 * @return array
	 */
	public static function values(array $array) : array
	{
		return array_values($array);
	}

	/**
	 * Randomize array.
	 *
	 * @access public
	 * @param array $array
	 * @param int $num
	 * @return mixed
	 */
	public static function rand(array $array, int $num = 1) : mixed
	{
		return array_rand($array, $num);
	}

	/**
	 * Slice array.
	 *
	 * @access public
	 * @param array $array
	 * @param int $offset
	 * @param int $length
	 * @param bool $preserve
	 * @return array
	 */
	public static function slice(array $array, int $offset, ?int $length = null, bool $preserve = false) : array
	{
		return array_slice($array, $offset, $length, $preserve);
	}

	/**
	 * Get array chunk.
	 *
	 * @access public
	 * @param array $array
	 * @param int $length
	 * @param bool $preserve
	 * @return array
	 */
	public static function chunk(array $array, int $length, bool $preserve = false) : array
	{
		return array_chunk($array, $length, $preserve);
	}

	/**
	 * Filter array.
	 *
	 * @access public
	 * @param array $array
	 * @param callable $callback
	 * @param int $mode
	 * @return array
	 */
	public static function filter(array $array, $callback = null, int $mode = 0) : array
	{
		if ( !TypeCheck::isNull($callback) ) {
			return array_filter($array, $callback, $mode);
		}
		return array_filter($array);
	}

	/**
	 * Format array.
	 *
	 * @access public
	 * @param array $array
	 * @return array
	 */
	public static function format(array $array) : array
	{
		return self::filter(
			self::values($array)
		);
	}

	/**
	 * Format array key case.
	 *
	 * [CASE_LOWER: 0]
	 *
	 * @access public
	 * @param array $array
	 * @param int $case
	 * @return array
	 */
	public static function formatKeyCase(array $array, int $case = 0) : array
	{
		return array_change_key_case($array, $case);
	}

	/**
	 * Walk recursive array.
	 *
	 * @access public
	 * @param mixed $array
	 * @param callable $callback
	 * @param mixed $arg
	 * @return bool
	 */
	public static function recursive(&$array, $callback, $arg = null) : bool
	{
		return array_walk_recursive($array, $callback, $arg);
	}

	/**
	 * Unique array.
	 *
	 * [SORT_STRING: 2].
	 *
	 * @access public
	 * @param array $array
	 * @param int $flags
	 * @return array
	 */
	public static function unique(array $array, int $flags = 2) : array
	{
		return array_unique($array, $flags);
	}

	/**
	 * Unique arrays.
	 *
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
	 * Sort array.
	 *
	 * @access public
	 * @param array $array
	 * @param mixed $orderby
	 * @param string $order
	 * @param bool $preserve (keys)
	 * @return array
	 */
	public static function sort(array $array, $orderby, string $order = 'ASC', bool $preserve = false) : array
	{
		if ( TypeCheck::isString($orderby) ) {
			$orderby = [$orderby => $order];
		}

		foreach ($orderby as $field => $dir) {
			$orderby[$field] = ('DESC' === Stringify::uppercase($dir)) ? 'DESC' : 'ASC';
		}

		$sort = function ($a, $b) use ($orderby) {

			$a = (array)$a;
			$b = (array)$b;

			foreach ($orderby as $field => $dir) {

				if ( !isset($a[$field]) || !isset($b[$field]) ) {
					continue;
				}

				if ( $a[$field] == $b[$field] ) {
					continue;
				}

				$val = ('DESC' === $dir) ? [1, -1] : [-1, 1];

				if ( TypeCheck::isNumeric($a[$field]) && TypeCheck::isNumeric($b[$field]) ) {
					return ($a[$field] < $b[$field]) ? $val[0] : $val[1];
				}

				return 0 > strcmp($a[$field], $b[$field]) ? $val[0] : $val[1];
			}

			return 0;
		};

		if ( $preserve ) {
			uasort($array, $sort);

		} else {
			usort($array, $sort);
		}

		return $array;
	}

	/**
	 * Pluck values from array of arrays/objects by key.
	 *
	 * @access public
	 * @param array $array
	 * @param string $key
	 * @param string|null $indexKey
	 * @return array
	 */
	public static function pluck(array $array, string $key, ?string $indexKey = null) : array
	{
		$result = [];

		foreach ($array as $index => $item) {
			$value = null;
			
			if ( TypeCheck::isArray($item) && isset($item[$key]) ) {
				$value = $item[$key];
			} elseif ( TypeCheck::isObject($item) && property_exists($item, $key) ) {
				$value = $item->{$key};
			}

			if ( $value !== null ) {
				if ( $indexKey !== null ) {
					$itemKey = null;
					if ( TypeCheck::isArray($item) && isset($item[$indexKey]) ) {
						$itemKey = $item[$indexKey];
					} elseif ( TypeCheck::isObject($item) && property_exists($item, $indexKey) ) {
						$itemKey = $item->{$indexKey};
					}
					
					if ( $itemKey !== null ) {
						$result[$itemKey] = $value;
					} else {
						$result[] = $value;
					}
				} else {
					$result[] = $value;
				}
			}
		}

		return $result;
	}

	/**
	 * Group array elements by key value.
	 *
	 * @access public
	 * @param array $array
	 * @param string $key
	 * @return array
	 */
	public static function groupBy(array $array, string $key) : array
	{
		$result = [];

		foreach ($array as $item) {
			$groupKey = null;
			
			if ( TypeCheck::isArray($item) && isset($item[$key]) ) {
				$groupKey = $item[$key];
			} elseif ( TypeCheck::isObject($item) && property_exists($item, $key) ) {
				$groupKey = $item->{$key};
			}

			if ( $groupKey !== null ) {
				if ( !isset($result[$groupKey]) ) {
					$result[$groupKey] = [];
				}
				$result[$groupKey][] = $item;
			}
		}

		return $result;
	}

	/**
	 * Flatten multidimensional array to specified depth.
	 *
	 * @access public
	 * @param array $array
	 * @param int $depth
	 * @return array
	 */
	public static function flatten(array $array, int $depth = 1) : array
	{
		$result = [];

		foreach ($array as $key => $value) {
			if ( TypeCheck::isArray($value) && $depth > 0 ) {
				$flattened = self::flatten($value, $depth - 1);
				$result = self::merge($result, $flattened);
			} else {
				$result[] = $value;
			}
		}

		return $result;
	}
}
