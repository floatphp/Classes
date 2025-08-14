<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file is a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Server\Date;

/**
 * Advanced session manipulation.
 */
final class Session
{
    /**
     * Start session if not active.
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
    public static function register(int $time = 60) : void
    {
        self::set('--session-id', session_id());
        self::set('--session-time', intval($time));

        $time = self::get('--session-time');
        self::set('--session-start', Date::newTime(h: 0, m: 0, s: $time));
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
    public static function get(?string $key = null) : mixed
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
    public static function set($key, $value = null) : void
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
    public static function isSetted(?string $key = null) : bool
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
    public static function unset(?string $key = null) : void
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
        return self::get('--session-start') < Date::timeNow();
    }

    /**
     * Renew session when the given time is not up.
     *
     * @access public
     * @return void
     */
    public static function renew() : void
    {
        $time = self::get('--session-time');
        self::set('--session-start', Date::newTime(h: 0, m: 0, s: $time));
    }

    /**
     * Get current session id.
     *
     * @access public
     * @return int
     */
    public static function getSessionId() : int
    {
        return (int)self::get('--session-id');
    }

    /**
     * Get session name.
     *
     * @access public
     * @return mixed
     */
    public static function getName() : mixed
    {
        return session_name();
    }

    /**
     * Get session status.
     *
     * [Disabled : 0].
     * [None     : 1].
     * [Active   : 2].
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
        $status = self::getStatus();
        return $status === 2;
    }

    /**
     * Close session (Read-only).
     *
     * @access public
     * @return bool
     */
    public static function close() : bool
    {
        return session_write_close();
    }

    /**
     * End session (Destroy).
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
