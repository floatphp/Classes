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

final class Converter
{
	/**
	 * Convert array to object.
	 * 
	 * @access public
	 * @param array $array
	 * @return object
	 */
	public static function toObject($array)
	{
	    return (object)Json::decode(
	    	Json::encode($array),
	    	false
	    );
	}

	/**
	 * Convert object to array.
	 * 
	 * @access public
	 * @param object $object
	 * @return array
	 */
	public static function toArray($object)
	{
	    return (array)Json::decode(
	    	Json::encode($object),
	    	true
	    );
	}
	
	/**
	 * Convert number to money.
	 * 
	 * @access public
	 * @param mixed $number
	 * @param int $decimals
	 * @param string $dSep Decimals Separator
	 * @param string $tSep Thousands Separator
	 * @return mixed
	 */
	public static function toMoney($number, $decimals = 2, $dSep = '.', $tSep = ' ')
	{
		return number_format((float)$number, $decimals, $dSep, $tSep);
	}
}
