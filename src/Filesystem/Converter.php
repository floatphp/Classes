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

use FloatPHP\Classes\Security\Tokenizer;

/**
 * Advanced types manipulation.
 */
final class Converter
{
	/**
	 * Convert array to object.
	 *
	 * @access public
	 * @param array $array
	 * @param bool $strict
	 * @return object
	 */
	public static function toObject(array $array, $strict = false) : object
	{
		if ( $strict ) {
			return (object)Json::decode(
				Json::encode($array)
			);
		}
		$object = new \stdClass;
		foreach ($array as $item => $val) {
			$object->{$item} = $val;
		}
		return (object)$object;
	}

	/**
	 * Convert object to array.
	 *
	 * @access public
	 * @param object $object
	 * @return array
	 */
	public static function toArray(object $object) : array
	{
		return (array)Json::decode(
			Json::encode($object),
			true
		);
	}

	/**
	 * Convert data to key.
	 *
	 * @access public
	 * @param mixed $data
	 * @return string
	 */
	public static function toKey($data) : string
	{
		return Tokenizer::hash(
			Stringify::serialize($data)
		);
	}

	/**
	 * Convert number to float.
	 * 
	 * @access public
	 * @param mixed $number
	 * @param int $decimals
	 * @param string $dSep Decimals Separator
	 * @param string $tSep Thousands Separator
	 * @return float
	 */
	public static function toFloat($number, int $decimals = 0, string $dSep = '.', string $tSep = '') : float
	{
		return (float)number_format($number, $decimals, $dSep, $tSep);
	}

	/**
	 * Convert number to money.
	 *
	 * @access public
	 * @inheritdoc
	 * @return string
	 */
	public static function toMoney($number, int $decimals = 2, string $dSep = '.', string $tSep = ',') : string
	{
		return (string)self::toFloat($number, $decimals, $dSep, $tSep);
	}

	/**
	 * Convert dynamic value type.
	 *
	 * @access public
	 * @param mixed $value
	 * @return mixed
	 * @internal
	 */
	public static function toType($value) : mixed
	{
		if ( TypeCheck::isArray($value) ) {
			if ( TypeCheck::isArray($value) ) {
				return Arrayify::map([self::class, 'toType'], $value);
			}
		}

		if ( ($match = TypeCheck::isDynamicType('bool', $value)) ) {
			return ($match === '1') ? true : false;
		}

		if ( ($match = TypeCheck::isDynamicType('int', $value)) ) {
			return ($match !== 'NaN') ? intval($match) : '';
		}

		if ( ($match = TypeCheck::isDynamicType('float', $value)) ) {
			return ($match !== 'NaN') ? floatval($match) : '';
		}

		return $value;
	}

	/**
	 * Convert data to text (database).
	 *
	 * @access public
	 * @param mixed $values
	 * @return string
	 * @internal
	 */
	public static function toText(mixed $value) : string
	{
		$value = Json::format($value, 256);
		return (string)Stringify::serialize($value);
	}

	/**
	 * Convert data from text (database).
	 *
	 * @access public
	 * @param string $values
	 * @return mixed
	 * @internal
	 */
	public static function fromText(string $value) : mixed
	{
		$value = Stringify::unserialize($value);
		if ( TypeCheck::isString($value) ) {
			$value = Json::decode($value, true);
		}
		return $value;
	}

	/**
	 * Convert types to string.
	 *
	 * @access public
	 * @param mixed $value
	 * @param bool $null Nullable
	 * @return string
	 * @internal
	 */
	public static function toString(mixed $value, bool $null = false) : string
	{
		return match (true) {
			TypeCheck::isFalse($value)         => 'false',
			TypeCheck::isTrue($value)          => 'true',
			TypeCheck::isArray($value)         => self::toText($value),
			TypeCheck::isObject($value)        => self::toText($value),
			TypeCheck::isNull($value) && $null => 'null',
			default                            => (string)$value
		};
	}
}
