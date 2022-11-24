<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.0.0
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2022 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Server\System;

final class Cookie
{
	/**
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public static function get($key = null)
	{
        if ( $key ) {
            return self::isSetted($key) ? $_COOKIE[$key] : null;
        }
        return self::isSetted() ? $_COOKIE : null;
	}

	/**
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @param array $options
	 * @return bool
	 */
	public static function set($key, $value = null, $options = [])
	{
		return setcookie($key,$value,$options);
	}
	
	/**
	 * @access public
	 * @param string $key
	 * @return bool
	 */
	public static function isSetted($key = null)
	{
        if ( $key ) {
            return isset($_COOKIE[$key]);
        }
        return isset($_COOKIE) && !empty($_COOKIE);
	}

	/**
	 * @access public
	 * @param void
	 * @return bool
	 */
	public static function clear()
	{
        if ( System::getIni('session.use_cookies') ) {
            $params = session_get_cookie_params();
            self::set(Session::getName(), '', [
            	'expires'  => time() - 42000,
            	'path'     => $params['path'],
            	'domain'   => $params['domain'],
            	'secure'   => $params['secure'],
            	'httponly' => $params['httponly'],
            	'samesite' => $params['samesite']
            ]);
            return true;
        }
        return false;
	}
}
