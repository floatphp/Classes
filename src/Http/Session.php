<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.0.0
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Server\Date;

final class Session
{
    /**
     * Start session.
     *
     * @param void
     */
    public function __construct()
    {
        if ( !self::isActive() ) {
            session_start();
        }
    }
    
    /**
     * Register session.
     *
     * @access public
     * @param int $time
     * @return void
     */
    public static function register($time = 60)
    {
        self::set('--session-id', session_id());
        self::set('--session-time', intval($time));
        self::set('--session-start', Date::newTime(0, 0, self::get('--session-time')));
    }

    /**
     * Check if session is registered.
     *
     * @access public
     * @param void
     * @return bool
     */
    public static function isRegistered()
    {
        if ( !empty(self::get('--session-id')) ) {
            return true;
        }
        return false;
    }

    /**
     * Set session value.
     *
     * @access public
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value = null)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Retrieve session value.
     *
     * @access public
     * @param string $item
     * @return mixed
     */
    public static function get($item = null)
    {
        if ( $item ) {
            return self::isSetted($item) ? $_SESSION[$item] : null;
        }
        return self::isSetted() ? $_SESSION : null;
    }

    /**
     * Retrieve session name.
     *
     * @access public
     * @param void
     * @return mixed
     */
    public static function getName()
    {
        return session_name();
    }

    /**
     * Check session key exists.
     *
     * @access public
     * @param string $key
     * @return bool
     */
    public static function isSetted($key = null)
    {
        if ( $key ) {
            return isset($_SESSION[$key]);
        }
        return isset($_SESSION) && !empty($_SESSION);
    }

    /**
     * Get current session id.
     *
     * @access public
     * @param void
     * @return int
     */
    public static function getSessionId()
    {
        return self::get('--session-id');
    }

    /**
     * Check if session is expired.
     *
     * @access public
     * @param void
     * @return bool
     */
    public static function isExpired() : bool
    {
        return (self::get('--session-start') < Date::timeNow());
    }

    /**
     * Renew session when the given time is not up.
     *
     * @access public
     * @param void
     * @return void
     */
    public static function renew()
    {
        self::set('--session-start', Date::newTime(0, 0, self::get('--session-time')));
    }

    /**
     * Check session is active.
     *
     * @access public
     * @param void
     * @return bool
     */
    public static function isActive()
    {
        return (session_status() === PHP_SESSION_ACTIVE);
    }
    
    /**
     * Close session.
     *
     * @access public
     * @param void
     * @return bool
     */
    public static function close()
    {
        return session_write_close();
    }

    /**
     * End session.
     *
     * @access public
     * @param void
     * @return bool
     */
    public static function end()
    {
        if ( self::isActive() ) {
            $_SESSION = [];
            return session_destroy();
        }
        return false;
    }
}
