<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.1.0
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

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
	    foreach ( $array as $item => $val ) {
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
	 * Convert number to float.
	 * 
	 * @access public
	 * @param mixed $number
	 * @param int $decimals
	 * @param string $dSep Decimals Separator
	 * @param string $tSep Thousands Separator
	 * @return float
	 */
	public static function toFloat($number, int $decimals = 0, string $dSep = '.', string $tSep = ',') : float
	{
		return (float)number_format($number, $decimals, $dSep, $tSep);
	}
	
	/**
	 * Convert number to money.
	 *
	 * @inheritdoc
	 */
	public static function toMoney($number, int $decimals = 2, string $dSep = '.', string $tSep = ' ') : float
	{
		return self::toFloat($number, $decimals, $dSep, $tSep);
	}
}
