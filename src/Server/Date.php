<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Security Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Server;

use \DateTime;

final class Date extends DateTime
{
	/**
	 * @access public
	 * @param string $date
	 * @param string $format
	 * @return object $date
	 */
	public static function get($date, $format = 'd/m/Y H:i:s')
	{
		$date = new self($date);
		$date->format($format);
		return $date;
	}

	/**
	 * @access public
	 * @param object $date
	 * @param object $expire
	 * @return mixed
	 */
	public static function difference($date, $expire)
	{
		$interval = $date->diff($expire)->format('%R%a');
		return intval($interval);
	}

	/**
	 * @access public
	 * @param string $date
	 * @param string $format
	 * @param string $to
	 * @return object $date
	 */
	public static function createFrom($date, $format, $to = 'd/m/Y H:i:s')
	{
		$date = self::createFromFormat($format, $date);
		$date->format($to);
		return $date;
	}

	/**
	 * @access public
	 * @param string $date
	 * @param string $format
	 * @param string $to
	 * @return string
	 */
	public static function toString($date, $format, $to = 'd/m/Y H:i:s')
	{
		return Date::createFrom($date,$format)->format($to);
	}

    /**
     * Return current time
     *
     * @access public
     * @param void
     * @return unix timestamp
     */
    public static function timeNow()
    {
        $currentHour = date('H');
        $currentMin  = date('i');
        $currentSec  = date('s');
        $currentMon  = date('m');
        $currentDay  = date('d');
        $currentYear = date('y');
        return mktime(
            $currentHour,
            $currentMin,
            $currentSec,
            $currentMon,
            $currentDay,
            $currentYear
        );
    }

    /**
     * Generates new time
     *
     * @access public
     * @param int $h
     * @param int $m
     * @param int $s
     * @param int $mt
     * @param int $d
     * @param int $y
     * @return unix timestamp
     */
    public static function newTime($h = 0, $m = 0, $s = 0, $mt = 0, $d = 0, $y = 0)
    {
        $currentHour = date('H');
        $currentMin  = date('i');
        $currentSec  = date('s');
        $currentMon  = date('m');
        $currentDay  = date('d');
        $currentYear = date('y');
        return mktime(
            ($currentHour + $h),
            ($currentMin + $m),
            ($currentSec + $s),
            ($currentMon + $mt),
            ($currentDay + $d),
            ($currentYear + $y)
        );
    }
}
