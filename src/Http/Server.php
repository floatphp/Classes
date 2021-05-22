<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Http Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Filesystem\Stringify;

final class Server
{
	/**
	 * Get global server variable
	 *
	 * @access public
	 * @param string $item
	 * @param bool $format
	 * @return mixed
	 */
	public static function get($item = null, $format = true)
	{
		if ( $item ) {
			if ( $format ) {
				$item = self::formatArgs($item);
			}
			return self::isSetted($item) ? $_SERVER[$item] : false;
		} else {
			return $_SERVER;
		}
	}

	/**
	 * Set global server variable
	 *
	 * @access public
	 * @param string $item
	 * @param mixed $value
	 * @param bool $format
	 * @return void
	 */
	public static function set($item, $value, $format = true)
	{
		if ( $format ) {
			$value = self::formatArgs($value);
		}
		$_SERVER[$item] = $value;
	}

	/**
	 * Check is global server variable setted
	 *
	 * @access public
	 * @param string $item
	 * @param bool $format
	 * @return bool
	 */
	public static function isSetted($item = null, $format = true)
	{
		if ( $item ) {
			if ( $format ) {
				$item = self::formatArgs($item);
			}
			return isset($_SERVER[$item]);
		} else {
			return isset($_SERVER);
		}
	}
	
	/**
	 * Get remote IP address
	 *
	 * @access public
	 * @param void
	 * @return mixed
	 */
	public static function getIP()
	{
		if ( self::isSetted('http-x-real-ip') ) {
			$ip = self::get('http-x-real-ip');
			return Stringify::slashStrip($ip);

		} elseif ( self::isSetted('http-x-forwarded-for') ) {
			$ip = self::get('http-x-forwarded-for');
			$ip = Stringify::slashStrip($ip);
			$ip = Stringify::split($ip, ['regex' => '/,/']);
			$ip = (string) trim(current($ip));
 			return self::isValidIP($ip);

		} elseif ( self::isSetted('remote-addr') ) {
			$ip = self::get('remote-addr');
			return Stringify::slashStrip($ip);
		}
		return false;
	}

	/**
	 * Get prefered protocol
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	public static function getProtocol()
	{
		return Server::isHttps() ? 'https://' : 'http://';
	}

	/**
	 * Get country code from request headers
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	public static function getCountryCode()
	{
		$headers = [
			'mm-country-code',
			'geoip-country-code',
			'http-cf-ipcountry',
			'http-x-country-code'
		];
		foreach ($headers as $header) {
			if ( self::isSetted($header) ) {
				$code = self::get($header);
				if ( !empty($code) ) {
					$code = Stringify::slashStrip($code);
					return Stringify::uppercase($code);
					break;
				}
			}
		}
		return false;
	}
	
	/**
	 * Redirect URL
	 *
	 * @access public
	 * @param string $url
	 * @param int $code
	 * @param string $message
	 * @return void
	 */
	public static function redirect($url = '/', $code = 301, $message = 'Moved Permanently')
	{
		header("Status: {$code} {$message}",false,$code);
		header("Location: {$url}");
		exit();
	}

	/**
	 * Get current URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	public static function getCurrentUrl()
	{
		$url = self::get('http-host') . self::get('request-uri');
		if ( self::isHttps() ) {
			return "https://{$url}";
		} else {
			return "http://{$url}";
		}
	}

	/**
	 * Check is authenticated
	 *
	 * @access public
	 * @param void
	 * @return bool
	 */
	public static function isBasicAuth()
	{
		if ( self::isSetted('php-auth-user') && self::isSetted('php-auth-pw') ) {
			return true;
		}
		return false;
	}

	/**
	 * Get authentication user
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	public static function getBasicAuthUser()
	{
		return self::isSetted('php-auth-user') ? self::get('php-auth-user') : '';
	}

	/**
	 * Get authentication password
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	public static function getBasicAuthPwd()
	{
		return self::isSetted('php-auth-pw') ? self::get('php-auth-pw') : '';
	}

	/**
	 * Get authorization header
	 *
	 * @access private
	 * @param void
	 * @return mixed
	 */
	public static function getAuthorizationHeaders()
	{
        if ( self::isSetted('Authorization',false) ) {
            return trim(self::get('Authorization',false));

        } elseif ( self::isSetted('http-authorization') ) {
            return trim(self::get('http-authorization'));

        } elseif ( function_exists('apache_request_headers') ) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
            	array_map('ucwords',array_keys($requestHeaders)),array_values($requestHeaders)
            );
            if ( isset($requestHeaders['Authorization']) ) {
                return trim($requestHeaders['Authorization']);
            }
        }
        return false;
    }

    /**
     * @access private
     * @param void
     * @return mixed
     */
    public static function getBearerToken()
    {
        if ( ($headers = self::getAuthorizationHeaders()) ) {
            return Stringify::match('/Bearer\s(\S+)/',$headers);
        }
        return false;
    }

	/**
	 * Check protocol is HTTPS
	 *
	 * @access public
	 * @param void
	 * @return bool
	 */
	public static function isHttps()
	{
		if ( self::isSetted('https') && !empty(self::get('https')) ) {
			if ( self::get('https') !== 'off' ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check IP address
	 *
	 * @access public
	 * @param string $ip
	 * @return string|false
	 */
	public static function isValidIP($ip)
	{
	    $pattern = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';
	    if ( Stringify::match($pattern,$ip) && self::isIPV6($ip) ) {
	        return $ip;
	    }
	    return false;
	}

	/**
	 * Check IPv6 address
	 *
	 * @access public
	 * @param string $ip
	 * @return bool
	 */
	public static function isIPV6($ip) : bool
	{
	    $ip = self::uncompressIPV6($ip);
	    list($ipv6, $ipv4) = self::splitIPV6($ip);
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
	 * Uncompresses IPv6 address
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	public static function uncompressIPV6($ip) : string
	{
	    if ( substr_count($ip, '::') !== 1 ) {
	        return $ip;
	    }
	    list($ip1, $ip2) = explode('::', $ip);
	    $c1 = ($ip1 === '') ? -1 : substr_count($ip1,':');
	    $c2 = ($ip2 === '') ? -1 : substr_count($ip2,':');
	 
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
	        $ip = Stringify::replace('::',$fill,$ip);

	    } else {
	        $fill = ':' . Stringify::repeat('0:', 6 - $c2 - $c1);
	        $ip = Stringify::replace('::',$fill,$ip);
	    }
	    return $ip;
	}

	/**
	 * Splits IPv6 address into the IPv6 and IPv4 representation parts
	 *
	 * @access public
	 * @param string $ip
	 * @return array
	 */
	public static function splitIPV6($ip) : array
	{
	    if ( strpos($ip,'.') !== false ) {
	        $pos = strrpos($ip,':');
	        $ipv6 = substr($ip,0,$pos);
	        $ipv4 = substr($ip,$pos + 1);
	        return [$ipv6,$ipv4];
	    }
	    return [$ip,''];
	}

	/**
	 * Format args
	 *
	 * @access public
	 * @param string $arg
	 * @return string
	 */
	public static function formatArgs($arg)
	{
	    $arg = Stringify::replace('-','_',$arg);
	    return Stringify::uppercase($arg);
	}
}
