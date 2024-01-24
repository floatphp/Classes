<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.1.1
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Server\Date;

final class Session
{
    /**
     * Start session.
     */
    public function __construct()
    {
        if ( !self::isActive() ) {
            @session_start();
        }
    }
    
    /**
     * Register session.
     *
     * @access public
     * @param int $time
     * @return void
     */
    public static function register(int $time = 60)
    {
        self::set('--session-id', session_id());
        self::set('--session-time', intval($time));
        self::set('--session-start', Date::newTime(0, 0, self::get('--session-time')));
    }

    /**
     * Check whether session is registered.
     *
     * @access public
     * @return bool
     */
    public static function isRegistered() : bool
    {
        if ( !empty(self::get('--session-id')) ) {
            return true;
        }
        return false;
    }

    /**
     * Get _SESSION value.
     * 
     * @access public
     * @param string $key
     * @return mixed
     */
    public static function get(?string $key = null)
    {
        if ( $key ) {
            return self::isSetted($key) ? $_SESSION[$key] : null;
        }
        return self::isSetted() ? $_SESSION : null;
    }

    /**
     * Set _SESSION value.
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
     * Check _SESSION value.
     * 
     * @access public
     * @param string $key
     * @return bool
     */
    public static function isSetted(?string $key = null)
    {
        if ( $key ) {
            return isset($_SESSION[$key]);
        }
        return isset($_SESSION) && !empty($_SESSION);
    }

    /**
     * Unset _SESSION value.
     * 
     * @access public
     * @param string $key
     * @return void
     */
    public static function unset(?string $key = null)
    {
        if ( $key ) {
            unset($_SESSION[$key]);

        } else {
            $_SESSION = [];
        }
    }

    /**
     * Check whether session is expired.
     *
     * @access public
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
     * @return void
     */
    public static function renew()
    {
        self::set('--session-start', Date::newTime(0, 0, self::get('--session-time')));
    }
    
    /**
     * Get current session id.
     *
     * @access public
     * @return int
     */
    public static function getSessionId()
    {
        return self::get('--session-id');
    }

    /**
     * Get session name.
     *
     * @access public
     * @return mixed
     */
    public static function getName()
    {
        return session_name();
    }

    /**
     * Get session status.
     * 
     * Disabled: 0
     * None: 1
     * Active: 2
     * 
     * @access public
     * @return int
     */
    public static function getStatus() : int
    {
        return session_status();
    }

    /**
     * Check whether session is active.
     *
     * @access public
     * @return bool
     */
    public static function isActive() : bool
    {
        return (self::getStatus() == 2);
    }
    
    /**
     * Close session (Write data).
     *
     * @access public
     * @return bool
     */
    public static function close() : bool
    {
        return session_write_close();
    }

    /**
     * End session (Destroy data).
     *
     * @access public
     * @return bool
     */
    public static function end() : bool
    {
        if ( !self::isActive() ) {
            new self();
        }
        self::unset();
        return @session_destroy();
    }
}
