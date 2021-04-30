<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Auth Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Auth;

final class Session
{
    /**
     * @param void
     */
    public function __construct()
    {
        if ( !self::isSetted() ) {
            session_start();
        }
    }
    
    /**
     * Register the session
     *
     * @access public
     * @param int $time
     * @return void
     */
    public static function register($time = 60)
    {
        self::set('session-id', session_id());
        self::set('session-time', intval($time));
        self::set('session-start', self::newTime());
    }

    /**
     * Check if session is registered
     *
     * @access public
     * @param void
     * @return bool
     */
    public static function isRegistered()
    {
        if ( !empty(self::get('session-id')) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set key in session
     *
     * @access public
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Retrieve value stored in session by key
     *
     * @access public
     * @param string $item
     * @return mixed
     */
    public static function get($item = null)
    {
        if ( $item ) {
            return self::isSetted($item) ? $_SESSION[$item] : false;
        } else {
            return $_SESSION;
        }
    }

    /**
     * Check key exists
     *
     * @access public
     * @param string $key
     * @return bool
     */
    public static function isSetted($key = null)
    {
        if ( $key ) {
            return isset($_SESSION[$key]);
        } else {
            return isset($_SESSION);
        }
    }

    /**
     * Retrieve global session variable
     *
     * @access public
     * @param void
     * @return array
     */
    public static function getSession()
    {
        return $_SESSION;
    }

    /**
     * Get id for current session
     *
     * @access public
     * @param void
     * @return int
     */
    public static function getSessionId()
    {
        return self::get('session-id');
    }

    /**
     * Check if session is over
     *
     * @access public
     * @param void
     * @return bool
     */
    public static function isExpired() : bool
    {
        if ( self::get('session-start') < self::timeNow() ) {
            return true;
        }
        return false;
    }

    /**
     * Renew session when the given time is not up
     *
     * @access public
     * @param void
     * @return void
     */
    public static function renew()
    {
        self::set('session-start', self::newTime());
    }

    /**
     * Return current time
     *
     * @access public
     * @param void
     * @return unix timestamp
     */
    private static function timeNow()
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
     * @param void
     * @return unix timestamp
     */
    private static function newTime()
    {
        $currentHour = date('H');
        $currentMin  = date('i');
        $currentSec  = date('s');
        $currentMon  = date('m');
        $currentDay  = date('d');
        $currentYear = date('y');
        return mktime(
            $currentHour,
            ($currentMin + self::get('session-time')),
            $currentSec,
            $currentMon,
            $currentDay,
            $currentYear
        );
    }

    /**
     * Destroy session
     *
     * @access public
     * @param void
     * @return void
     */
    public static function end()
    {
        session_destroy();
        $_SESSION = [];
    }
}
