<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Auth Component
 * @version   : 1.1.0
 * @category  : PHP framework
 * @copyright : (c) JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace floatPHP\Classes\Auth;

class Session
{
    /**
     * @param void
     * @return void
     */
    public function __construct()
    {
        if ( !isset($_SESSION) ) {
            session_start();
        }
    }
    
    /**
     * Register the session
     *
     * @param int $time
     * @return void
     */
    public function register($time = 60)
    {
        $_SESSION['session_id'] = session_id();
        $_SESSION['session_time'] = intval($time);
        $_SESSION['session_start'] = $this->newTime();
    }

    /**
     * Checks to see if the session is registered
     *
     * @param void
     * @return boolean
     */
    public function isRegistered()
    {
        if (! empty($_SESSION['session_id'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set key/value in session
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Retrieve value stored in session by key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
    }

    /**
     * Retrieve the global session variable
     *
     * @return array
     */
    public function getSession()
    {
        return $_SESSION;
    }

    /**
     * Gets the id for the current session
     *
     * @return integer - session id
     */
    public function getSessionId()
    {
        return $_SESSION['session_id'];
    }

    /**
     * Checks to see if the session is over based on the amount of time given
     *
     * @return boolean
    */
    public function isExpired()
    {
        if ($_SESSION['session_start'] < $this->timeNow() ) {
            return false;
        } else {
            return false;
        }
    }

    /**
     * Renews the session when the given time is not up and there is activity on the site.
     */
    public function renew()
    {
        $_SESSION['session_start'] = $this->newTime();
    }

    /**
     * Returns the current time
     *
     * @return unix timestamp
     */
    private function timeNow()
    {
        $currentHour = date('H');
        $currentMin = date('i');
        $currentSec = date('s');
        $currentMon = date('m');
        $currentDay = date('d');
        $currentYear = date('y');
        return mktime($currentHour, $currentMin, $currentSec, $currentMon, $currentDay, $currentYear);
    }

    /**
     * Generates new time
     *
     * @return unix timestamp
     */
    private function newTime()
    {
        $currentHour = date('H');
        $currentMin = date('i');
        $currentSec = date('s');
        $currentMon = date('m');
        $currentDay = date('d');
        $currentYear = date('y');
        return mktime($currentHour, ($currentMin + $_SESSION['session_time']), $currentSec, $currentMon, $currentDay, $currentYear);
    }

    /**
     * Destroys the session
     */
    public function end()
    {
        session_destroy();
        $_SESSION = [];
    }
}
