<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.2.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

use FloatPHP\Classes\Http\Server;
use FloatPHP\Classes\Server\System;

class Validator
{
	/**
	 * Validate email.
	 *
	 * @access public
	 * @param mixed $email
	 * @return bool
	 */
	public static function isValidEmail($email) : bool
	{
		return (bool)Stringify::filter($email, null, FILTER_VALIDATE_EMAIL);
	}

    /**
     * Validate URL.
     *
     * @access public
     * @param mixed $url
     * @return bool
     */
    public static function isValidUrl($url) : bool
    {
    	return (bool)Stringify::filter($url, null, FILTER_VALIDATE_URL);
    }

    /**
     * Validate date.
     *
     * @access public
     * @param mixed $date
     * @param bool $time
     * @return bool
     */
    public static function isValidDate($date, bool $time = false) : bool
    {
		// Object
		if ( TypeCheck::isObject($date) ) {
			return ($date instanceof \DateTime);
		}

		// String
		$date = Stringify::lowercase((string)$date);
		if ( $date == 'now' ) {
			return true;
		}

		// Regex
		$pattern = '/^(\d{4})[-,\/](\d{2})[-,\/](\d{2})$/';
		if ( $time ) {
			$pattern = '/(\d{2,4})[-,\/](\d{2})[-,\/](\d{2,4})[ ,T](\d{2})/i';
		}

		return Stringify::match($pattern, $date, $matches);
    }

    /**
     * Validate IP.
     *
	 * @access public
	 * @param string $ip
	 * @return bool
	 */
	public static function isValidIp(string $ip) : bool
	{
	    $pattern = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';
		$match   = Stringify::match($pattern, $ip, $matches);
	    return ($match || self::isIpV6($ip));
	}

    /**
     * Validate MAC.
     *
     * @access public
     * @param string $address
     * @return bool
     */
    public static function isValidMac(string $address) : bool
    {
		$pattern = "/^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/i";
		return Stringify::match($pattern, $address, $matches);
    }

	/**
	 * Validate file mime type.
	 *
	 * @access public
	 * @param string $file
	 * @param array $types
	 * @return bool
	 * @todo
	 */
	public static function isValidMime(string $file, ?array $types = null) : bool
	{
		return true;
	}

	/**
	 * Validate PHP module.
	 *
	 * @access public
	 * @param string $ext
	 * @return bool
	 */
	public static function isModule(string $module) : bool
	{
		return extension_loaded($module);
	}

	/**
	 * Validate server module.
	 *
	 * @access public
	 * @param string $module
	 * @return bool
	 */
	public static function isServerModule(string $module) : bool
	{
		$module = Stringify::undash($module);
		return Arrayify::inArray($module, Server::getModules());
	}

	/**
	 * Validate server config.
	 * 
	 * @access public
	 * @param string $name
	 * @param mixed $value
	 * @return bool
	 */
	public static function isConfig(string $name, $value) : bool
	{
		return (System::getIni($name) == $value);
	}

	/**
	 * Validate version.
	 *
	 * @access public
	 * @param string $v1
	 * @param string $v2
	 * @param string $operator
	 * @return bool
	 */
	public static function isVersion(string $v1, string $v2, string $operator = '==') : bool
	{
		return version_compare($v1, $v2, $operator);
	}

	/**
	 * Check IPv6 address.
	 *
	 * @access public
	 * @param string $ip
	 * @return bool
	 */
	public static function isIpV6($ip) : bool
	{
	    $ip = self::uncompressIpV6($ip);
	    list($ipv6, $ipv4) = self::splitIpV6($ip);
	    $ipv6 = explode(':',$ipv6);
	    $ipv4 = explode('.',$ipv4);
	    if ( count($ipv6) === 8 && count($ipv4) === 1 || count($ipv6) === 6 && count($ipv4) === 4 ) {
	        foreach ($ipv6 as $part) {
	            if ( $part === '' ) {
	                return false;
	            }
	            if ( strlen($part) > 4 ) {
	                return false;
	            }
	            $part = ltrim($part,'0');
	            if ( $part === '' ) {
	                $part = '0';
	            }
	            $value = hexdec($part);
	            if ( dechex($value) !== strtolower($part) || $value < 0 || $value > 0xFFFF ) {
	                return false;
	            }
	        }
	        if ( count($ipv4) === 4 ) {
	            foreach ($ipv4 as $part) {
	                $value = (int) $part;
	                if ( (string) $value !== $part || $value < 0 || $value > 0xFF ) {
	                    return false;
	                }
	            }
	        }
	        return true;
	    }
	    return false;
	}

	/**
	 * Uncompresses IPv6 address.
	 *
	 * @access private
	 * @return string
	 */
	private static function uncompressIpV6($ip) : string
	{
	    if ( substr_count($ip, '::') !== 1 ) {
	        return $ip;
	    }
	    list($ip1, $ip2) = explode('::', $ip);
	    $c1 = ($ip1 === '') ? -1 : substr_count($ip1, ':');
	    $c2 = ($ip2 === '') ? -1 : substr_count($ip2, ':');
	 
	    if ( strpos($ip2, '.') !== false ) {
	        $c2++;
	    }
	    if ( $c1 === -1 && $c2 === -1 ) {
	        $ip = '0:0:0:0:0:0:0:0';

	    } elseif ( $c1 === -1 ) {
	        $fill = Stringify::repeat('0:', 7 - $c2);
	        $ip = Stringify::replace('::', $fill, $ip);

	    } elseif ( $c2 === -1 ) {
	        $fill = Stringify::repeat(':0', 7 - $c1);
	        $ip = Stringify::replace('::', $fill, $ip);

	    } else {
	        $fill = ':' . Stringify::repeat('0:', 6 - $c2 - $c1);
	        $ip = Stringify::replace('::', $fill, $ip);
	    }
	    return $ip;
	}

	/**
	 * Splits IPv6 address into the IPv6 and IPv4 parts.
	 *
	 * @access private
	 * @param string $ip
	 * @return array
	 */
	private static function splitIpV6($ip) : array
	{
	    if ( strpos($ip, '.') !== false ) {
	        $pos = (int)strrpos($ip, ':');
	        $ipv6 = substr($ip, 0, $pos);
	        $ipv4 = substr($ip, $pos + 1);
	        return [$ipv6, $ipv4];
	    }
	    return [$ip, ''];
	}
}
