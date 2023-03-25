<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.0.2
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2023 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

class Validator
{
    /**
     * Validate email.
     *
	 * @access public
	 * @param string $email
	 * @return bool
	 */
	public static function isValidEmail($email) : bool
	{
		return (bool)Stringify::filter((string)$email, null, FILTER_VALIDATE_EMAIL);
	}

    /**
     * Validate URL.
     *
     * @access public
     * @param string $url
     * @return bool
     */
    public static function isValidUrl($url) : bool
    {
    	return (bool)Stringify::filter((string)$url, null, FILTER_VALIDATE_URL);
    }

    /**
     * Validate IP address.
     *
	 * @access public
	 * @param string $ip
	 * @return mixed
	 */
	public static function isValidIP($ip)
	{
	    $pattern = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';
	    if ( Stringify::match($pattern,$ip) || self::isIPv6($ip) ) {
	        return $ip;
	    }
	    return false;
	}

    /**
     * Validate mime type.
     *
	 * @access public
	 * @param string $filename
	 * @param array $mimes
	 * @return bool
	 */
	public static function isValidMime($filename, $mimes = []) : bool
	{
		if ( TypeCheck::isArray($mimes) && !empty($mimes) ) {
			foreach ( $mimes as $key => $value ) {
				$search = '!\.(' . $key . ')$!i';
				if ( Stringify::match($search, $filename, 1) ) {
					return true;
					break;
				}
			}
		}
		return false;
	}

    /**
     * Validate MAC address.
     *
     * @access public
     * @param string $address
     * @return bool
     */
    public static function isValidMAC($address) : bool
    {
        return (bool)Stringify::match(
        	"/^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/i",
        	$address
        );
    }

	/**
	 * Check IPv6 address.
	 *
	 * @access public
	 * @param string $ip
	 * @return bool
	 */
	public static function isIPv6($ip) : bool
	{
	    $ip = self::uncompressIPv6($ip);
	    list($ipv6, $ipv4) = self::splitIPv6($ip);
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
	 * @param void
	 * @return string
	 */
	private static function uncompressIPv6($ip) : string
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
	        $ip = Stringify::replace('::',$fill,$ip);

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
	private static function splitIPv6($ip) : array
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
