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

use FloatPHP\Classes\Filesystem\{
	Stringify, Arrayify, Validator
};

final class Server
{
	/**
	 * Get _SERVER value.
	 * 
	 * @access public
	 * @param string $key
	 * @param bool $format
	 * @return mixed
	 */
	public static function get(?string $key = null, $format = true)
	{
		if ( $key ) {
			if ( $format ) {
				$key = self::formatArgs($key);
			}
			return self::isSetted($key) ? $_SERVER[$key] : null;
		}
		return self::isSetted() ? $_SERVER : null;
	}

	/**
	 * Set _SERVER value.
	 * 
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @param bool $format
	 * @return void
	 */
	public static function set(string $key, $value = null, $format = true)
	{
		if ( $format ) {
			$value = self::formatArgs($value);
		}
		$_SERVER[$key] = $value;
	}

	/**
	 * Check _SERVER value.
	 * 
	 * @access public
	 * @param string $key
	 * @param bool $format
	 * @return bool
	 */
	public static function isSetted(?string $key = null, $format = true)
	{
		if ( $key ) {
			if ( $format ) {
				$key = self::formatArgs($key);
			}
			return isset($_SERVER[$key]);
		}
		return isset($_SERVER) && !empty($_SERVER);
	}

	/**
	 * Unset _SERVER value.
	 * 
	 * @access public
	 * @param string $key
	 * @return void
	 */
	public static function unset(?string $key = null)
	{
		if ( $key ) {
			unset($_SERVER[$key]);

		} else {
			$_SERVER = [];
		}
	}

	/**
	 * Get remote IP address.
	 *
	 * @access public
	 * @param string $domain
	 * @return mixed
	 */
	public static function getIp(?string $domain = null)
	{
		if ( $domain ) {
			$ip = gethostbyname($domain);
			return Validator::isValidIp($ip);
		}

		if ( self::isSetted('http-x-real-ip') ) {
			$ip = self::get('http-x-real-ip');
			return Stringify::stripSlash($ip);

		} elseif ( self::isSetted('http-x-forwarded-for') ) {
			$ip = self::get('http-x-forwarded-for');
			$ip = Stringify::stripSlash($ip);
			$ip = Stringify::split($ip, ['regex' => '/,/']);
			$ip = (string)trim(current($ip));
 			return Validator::isValidIp($ip);

		} elseif ( self::isSetted('http-cf-connecting-ip') ) {
			$ip = self::get('http-cf-connecting-ip');
			$ip = Stringify::stripSlash($ip);
			$ip = Stringify::split($ip, ['regex' => '/,/']);
			$ip = (string)trim(current($ip));
 			return Validator::isValidIp($ip);

		} elseif ( self::isSetted('remote-addr') ) {
			$ip = self::get('remote-addr');
			return Stringify::stripSlash($ip);
		}

		return false;
	}

	/**
	 * Get prefered protocol.
	 *
	 * @access public
	 * @return string
	 */
	public static function getProtocol()
	{
		return self::isSsl() ? 'https://' : 'http://';
	}

	/**
	 * Get country code from request headers.
	 *
	 * @access public
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
					$code = Stringify::stripSlash($code);
					return Stringify::uppercase($code);
					break;
				}
			}
		}
		return false;
	}
	
	/**
	 * Redirect URL.
	 *
	 * @access public
	 * @param string $url
	 * @param int $code
	 * @return void
	 */
	public static function redirect(string $url = '/', int $code = 0)
	{
		if ( $code ) {
			$message = Response::getMessage($code);
			header("Status: {$code} {$message}", true, $code);
		}
		header("Location: {$url}", true, $code);
		exit();
	}

	/**
	 * Get base URL.
	 *
	 * @access public
	 * @return string
	 */
	public static function getBaseUrl() : string
	{
		$url = self::get('http-host');
		if ( self::isSsl() ) {
			return "https://{$url}";
		}
		return "http://{$url}";
	}

	/**
	 * Get current URL.
	 *
	 * @access public
	 * @param bool $escape
	 * @return string
	 */
	public static function getCurrentUrl($escape = false) : string
	{
		$url = self::getBaseUrl() . self::get('request-uri');
		if ( $escape ) {
			$url = Stringify::parseUrl($url);
			if ( isset($url['query']) ) {
				unset($url['query']);
			}
			$url = rtrim("{$url['scheme']}://{$url['host']}{$url['path']}");
		}
		return $url;
	}

	/**
	 * Parse base from URL.
	 *
	 * @access public
	 * @param string $url
	 * @return string
	 */
	public static function parseBaseUrl($url = '')
	{
		if ( !empty($url) && ($url = Stringify::parseUrl($url)) ) {
			unset($url['path']);
			$tmp = '';
			if ( isset($url['scheme']) ) {
				$tmp = "{$url['scheme']}://";
			}
			if ( isset($url['host']) ) {
				$tmp = "{$tmp}{$url['host']}";
			}
			$url = $tmp;
		}
		return (string)$url;
	}

	/**
	 * Check basic authentication.
	 *
	 * @access public
	 * @return bool
	 */
	public static function isBasicAuth() : bool
	{
		if ( self::isSetted('php-auth-user') && self::isSetted('php-auth-pw') ) {
			return true;
		}
		return false;
	}

	/**
	 * Get basic authentication user.
	 *
	 * @access public
	 * @return string
	 */
	public static function getBasicAuthUser() : string
	{
		return self::isSetted('php-auth-user') ? self::get('php-auth-user') : '';
	}

	/**
	 * Get get basic authentication password.
	 *
	 * @access public
	 * @return string
	 */
	public static function getBasicAuthPwd() : string
	{
		return self::isSetted('php-auth-pw') ? self::get('php-auth-pw') : '';
	}

	/**
	 * Get authorization header.
	 *
	 * @access public
	 * @return mixed
	 */
	public static function getAuthorizationHeaders()
	{
        if ( self::isSetted('Authorization', false) ) {
            return trim(self::get('Authorization', false));

        } elseif ( self::isSetted('http-authorization') ) {
            return trim(self::get('http-authorization'));

        } elseif ( function_exists('apache_request_headers') ) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = Arrayify::combine(
            	Arrayify::map('ucwords', Arrayify::keys($requestHeaders)),
            	Arrayify::values($requestHeaders)
            );
            if ( isset($requestHeaders['Authorization']) ) {
                return trim($requestHeaders['Authorization']);
            }
        }
        return false;
    }

	/**
	 * Get authorization token.
	 *
     * @access public
     * @return string
     */
    public static function getBearerToken() : string
    {
		$token = false;
        if ( ($headers = self::getAuthorizationHeaders()) ) {
            $token = Stringify::match('/Bearer\s(\S+)/', $headers, 1);
        }
        return (string)$token;
    }

	/**
	 * Check whether protocol is HTTPS (SSL).
	 *
	 * @access public
	 * @return bool
	 */
	public static function isSsl() : bool
	{
		if ( self::isSetted('https') && !empty(self::get('https')) ) {
			if ( self::get('https') !== 'off' ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Format args.
	 *
	 * @access private
	 * @param string $arg
	 * @return string
	 * @internal
	 */
	private static function formatArgs($arg)
	{
	    $arg = Stringify::replace('-', '_', $arg);
	    return Stringify::uppercase($arg);
	}
}
