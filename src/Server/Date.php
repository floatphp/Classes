<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Server Component
 * @version    : 1.0.2
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2023 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Server;

use FloatPHP\Classes\Filesystem\{
    TypeCheck, Stringify
};
use \DateTime;
use \DateInterval;

final class Date extends DateTime
{
	/**
	 * @access public
	 * @param string $date
	 * @param string $format
     * @param bool $object
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
        return (int)$interval;
    }

	/**
	 * @access public
	 * @param string $date
	 * @param string $format
	 * @param string $to
	 * @return object
	 */
	public static function create($date, $format, $to = 'Y-m-d H:i:s')
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
		return self::create($date,$format)->format($to);
	}
    
    /**
     * @access public
     * @param string $dates
     * @param string $format
     * @return array
     */
    public static function order($dates = [], $sort = 'asc', $format = 'Y-m-d H:i:s')
    {
        usort($dates,function($a, $b) use ($sort,$format) {
            if ( Stringify::lowercase($sort) == 'asc' ) {
                return self::create($a,$format) <=> self::create($b,$format);

            } elseif ( Stringify::lowercase($sort) == 'desc' ) {
                return self::create($b,$format) <=> self::create($a,$format);
            }
        });
        return $dates;
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
            (int)$currentHour,
            (int)$currentMin,
            (int)$currentSec,
            (int)$currentMon,
            (int)$currentDay,
            (int)$currentYear
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
            (int)$date->format('H'),
            (int)$date->format('i'),
            (int)$date->format('s'),
            (int)$date->format('m'),
            (int)$date->format('d'),
            (int)$date->format('Y')
        );
        $expire = $date->add(new DateInterval($duration));
        $limit = mktime(
            (int)$expire->format('H'),
            (int)$expire->format('i'),
            (int)$expire->format('s'),
            (int)$expire->format('m'),
            (int)$expire->format('d'),
            (int)$expire->format('Y')
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
