<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Server Component
 * @version    : 1.1.0
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Server;

use FloatPHP\Classes\Filesystem\{
    TypeCheck, Stringify, Validator
};
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
    public static function get(string $date = 'now', string $to = self::FORMAT, bool $isObject = false)
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
     * @param self $date
     * @param string $to
     * @return string
     */
    public static function toString(self $date, string $to = self::FORMAT) : string
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
        if ( !self::isValid($date) || !self::isValid($expire) ) {
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
        if ( $i ) {
            $interval = $date->diff($expire)->format($i);

        } else {
            $interval = ($expire->getTimestamp() - $date->getTimestamp());
        }

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
        usort($dates, function($a, $b) use ($sort, $format) {
            if ( Stringify::lowercase($sort) == 'asc' ) {
                return self::create($a, $format) <=> self::create($b, $format);

            } elseif ( Stringify::lowercase($sort) == 'desc' ) {
                return self::create($b, $format) <=> self::create($a, $format);
            }
        });
        return $dates;
    }

    /**
     * Return current time.
     *
     * @access public
     * @return int
     */
    public static function timeNow() : int
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
        $currentHour = date('H');
        $currentMin  = date('i');
        $currentSec  = date('s');
        $currentMon  = date('m');
        $currentDay  = date('d');
        $currentYear = date('y');
        return mktime(
            ($currentHour + $h),
            ($currentMin  + $m),
            ($currentSec  + $s),
            ($currentMon  + $mt),
            ($currentDay  + $d),
            ($currentYear + $y)
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
        if ( !self::maybeDuration($duration) 
          || !self::isValid($date) ) {
            return -1;
        }

        // Get date
        if ( !self::isObject($date) ) {
            $date = new self($date);
        }
        
        // Set now
        $now = mktime(
            (int)$date->format('H'),
            (int)$date->format('i'),
            (int)$date->format('s'),
            (int)$date->format('m'),
            (int)$date->format('d'),
            (int)$date->format('Y')
        );

        // Get limit
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
     * Validate date.
     * 
     * @access public
     * @param mixed $date
     * @return bool
     */
    public static function isValid($date) : bool
    {
        if ( TypeCheck::isString($date) ) {
            if ( Stringify::lowercase($date) == 'now' 
              || Validator::isValidDate($date, true) ) {
                return true;
            }
        }
        return self::isObject($date);
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
        return ($date instanceof DateTime);
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
        return (strpos($duration, 'p', 0) !== false);
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
