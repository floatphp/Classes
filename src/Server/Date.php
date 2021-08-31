<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Server Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Server;

use FloatPHP\Classes\Filesystem\TypeCheck;
use \DateTime;
use \DateInterval;

final class Date extends DateTime
{
	/**
	 * @access public
	 * @param string $date
	 * @param string $format
	 * @return mixed
	 */
	public static function get($date = 'now', $format = 'Y-m-d H:i:s', $object = false)
	{
		$date = new self($date);
        if ( $object ) {
            $date->format($format);
            return $date;
        }
        return $date->format($format);
	}

    /**
     * @access public
     * @param mixed $date
     * @param mixed $expire
     * @param string $format
     * @return int
     */
    public static function difference($date, $expire, $format = null)
    {
        if ( TypeCheck::isString($date) ) {
            $date = new self($date);
        }
        if ( TypeCheck::isString($expire) ) {
            $expire = new self($expire);
        }
        if ( $format ) {
            // '%R%a'
            $interval = $date->diff($expire)->format($format);
        } else {
            $interval = $expire->getTimestamp() - $date->getTimestamp();
        }
        return intval($interval);
    }

	/**
	 * @access public
	 * @param string $date
	 * @param string $format
	 * @param string $to
	 * @return object
	 */
	public static function createFrom($date, $format, $to = 'Y-m-d H:i:s')
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
	public static function toString($date, $format, $to = 'Y-m-d H:i:s') : string
	{
		return self::createFrom($date,$format)->format($to);
	}
    
    /**
     * Return current time
     *
     * @access public
     * @param void
     * @return int
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
     * @return int
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

    /**
     * @access public
     * @param string $duration
     * @param string $date
     * @return int
     */
    public static function expireIn($duration = 'P1Y', $date = 'now') : int
    {
        $date = new self($date);
        $now = mktime(
            $date->format('H'),
            $date->format('i'),
            $date->format('s'),
            $date->format('m'),
            $date->format('d'),
            $date->format('Y')
        );
        $expire = $date->add(new DateInterval($duration));
        $limit = mktime(
            $expire->format('H'),
            $expire->format('i'),
            $expire->format('s'),
            $expire->format('m'),
            $expire->format('d'),
            $expire->format('Y')
        );
        return (int)$limit - $now;
    }

    /**
     * @access public
     * @param string $timezone
     * @return void
     */
    public static function setDefaultTimezone($timezone = '')
    {
        date_default_timezone_set($timezone);
    }
}
