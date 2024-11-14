<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Server Component
 * @version    : 1.3.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Server;

use FloatPHP\Classes\Filesystem\{Stringify, Validator};
use \DateTime;
use \DateInterval;

final class Date extends DateTime
{
    /**
     * @access public
     * @var string FORMAT, Date format
     */
    public const FORMAT = 'Y-m-d H:i:s';

    /**
     * Get date (Default current).
     *
     * @access public
     * @param string $date
     * @param string $to
     * @param bool $isObject
     * @return mixed
     */
    public static function get(string $date = 'now', string $to = self::FORMAT, bool $isObject = false) : mixed
    {
        $date = new self($date);
        $formatted = $date->format($to);
        if ( $isObject ) {
            return $date;
        }
        return $formatted;
    }

    /**
     * Create date object from string.
     * 
     * @access public
     * @param string $date
     * @param string $format
     * @param string $to
     * @return object
     */
    public static function create(string $date, string $format, string $to = self::FORMAT) : object
    {
        $date = self::createFromFormat($format, $date);
        $date->format($to);
        return $date;
    }

    /**
     * Convert date format.
     * 
     * @access public
     * @param string $date
     * @param string $format
     * @param string $to
     * @return string
     */
    public static function convert(string $date, string $format, string $to = self::FORMAT) : string
    {
        return self::create($date, $format)->format($to);
    }

    /**
     * Convert date object to string.
     * 
     * @access public
     * @param object $date
     * @param string $to
     * @return string
     */
    public static function toString(DateTime $date, string $to = self::FORMAT) : string
    {
        return $date->format($to);
    }

    /**
     * Get date difference interval,
     * Returns -1 if invalid date or expire.
     * 
     * @access public
     * @param mixed $date
     * @param mixed $expire
     * @param string $i Interval format '%R%a'
     * @param string $to
     * @return int
     */
    public static function difference($date, $expire, ?string $i = null, string $to = self::FORMAT) : int
    {
        // Check date
        if ( !Validator::isValidDate($date) || !Validator::isValidDate($expire) ) {
            return -1;
        }

        // Set beginning date
        if ( !self::isObject($date) ) {
            $date = new self($date);
        }
        $date->format($to);

        // Set expiring date
        if ( !self::isObject($expire) ) {
            $expire = new self($expire);
        }
        $expire->format($to);

        // Format difference interval
        $interval = $i ? $date->diff($expire)->format($i)
            : $expire->getTimestamp() - $date->getTimestamp();

        return (int)$interval;
    }

    /**
     * Order dates.
     * 
     * @access public
     * @param array $dates
     * @param string $format
     * @return array
     */
    public static function order(array $dates, $sort = 'asc', string $format = self::FORMAT) : array
    {
        usort($dates, function ($a, $b) use ($sort, $format) : int {
            if ( Stringify::lowercase($sort) == 'asc' ) {
                return self::create($a, $format) <=> self::create($b, $format);
            }
            if ( Stringify::lowercase($sort) == 'desc' ) {
                return self::create($b, $format) <=> self::create($a, $format);
            }
        });
        return $dates;
    }

    /**
     * Get current time.
     *
     * @access public
     * @return int
     */
    public static function timeNow() : int
    {
        $currentHour = date('H');
        $currentMin = date('i');
        $currentSec = date('s');
        $currentMon = date('m');
        $currentDay = date('d');
        $currentYear = date('y');
        return mktime(
            hour: (int)$currentHour,
            minute: (int)$currentMin,
            second: (int)$currentSec,
            month: (int)$currentMon,
            day: (int)$currentDay,
            year: (int)$currentYear
        );
    }

    /**
     * Generate new time.
     *
     * @access public
     * @param int $h, Hour
     * @param int $m, Minut
     * @param int $s, Second
     * @param int $mt, Month
     * @param int $d, Day
     * @param int $y, Year
     * @return int
     */
    public static function newTime($h = 0, $m = 0, $s = 0, $mt = 0, $d = 0, $y = 0) : int
    {
        // Get current
        $currentHour = date('H');
        $currentMin = date('i');
        $currentSec = date('s');
        $currentMon = date('m');
        $currentDay = date('d');
        $currentYear = date('y');

        return mktime(
            $currentHour + $h,
            $currentMin + $m,
            $currentSec + $s,
            $currentMon + $mt,
            $currentDay + $d,
            $currentYear + $y
        );
    }

    /**
     * Get date expiring interval using duration string,
     * Returns -1 if invalid date or duration.
     *
     * @access public
     * @param string $duration
     * @param mixed $date
     * @return int
     * @see https://www.php.net/manual/fr/dateinterval.construct.php
     */
    public static function expireIn(string $duration = 'P1Y', $date = 'now') : int
    {
        // Check date
        if (
            !self::maybeDuration($duration)
            || !Validator::isValidDate($date)
        ) {
            return -1;
        }

        // Get date
        if ( !self::isObject($date) ) {
            $date = new self($date);
        }

        // Set now
        $now = mktime(
            hour: (int)$date->format('H'),
            minute: (int)$date->format('i'),
            second: (int)$date->format('s'),
            month: (int)$date->format('m'),
            day: (int)$date->format('d'),
            year: (int)$date->format('Y')
        );

        // Get limit
        $expire = $date->add(new DateInterval($duration));
        $limit = mktime(
            hour: (int)$expire->format('H'),
            minute: (int)$expire->format('i'),
            second: (int)$expire->format('s'),
            month: (int)$expire->format('m'),
            day: (int)$expire->format('d'),
            year: (int)$expire->format('Y')
        );

        return (int)$limit - $now;
    }

    /**
     * Check date object.
     * 
     * @access public
     * @param mixed $date
     * @return bool
     */
    public static function isObject($date) : bool
    {
        return $date instanceof DateTime;
    }

    /**
     * Check date duration.
     * 
     * @access public
     * @param string $duration
     * @return bool
     */
    public static function maybeDuration(string $duration) : bool
    {
        $duration = Stringify::lowercase($duration);
        return strpos($duration, 'p', 0) !== false;
    }

    /**
     * Set default date timezone.
     * 
     * @access public
     * @param string $timezone
     * @return bool
     */
    public static function setDefaultTimezone(string $timezone) : bool
    {
        return date_default_timezone_set($timezone);
    }
}
