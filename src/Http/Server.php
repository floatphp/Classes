<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.2.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Filesystem\{
	Stringify, Arrayify, TypeCheck, Validator
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
			if ( $format ) $key = Stringify::undash($key, true);
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
		if ( $format ) $value = Stringify::undash($key, true);
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
	public static function isSetted(?string $key = null, $format = true) : bool
	{
		if ( $key ) {
			if ( $format ) $key = Stringify::undash($key, true);
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
	 * Get protocol.
	 *
	 * @access public
	 * @return string
	 */
	public static function getProtocol()
	{
		return (self::isSsl()) ? 'https://' : 'http://';
	}

	/**
	 * Get country code from request headers.
	 *
	 * @access public
	 * @param array $headers
	 * @return string
	 */
	public static function getCountryCode(array $headers = [])
	{
		$headers = Arrayify::merge([
			'mm-country-code',
			'geoip-country-code',
			'http-cf-ipcountry',
			'http-x-country-code'
		], $headers);

		foreach ($headers as $header) {
			if ( self::isSetted($header) ) {
				$code = self::get($header);
				if ( !empty($code) ) {
					$code = Stringify::stripSlash($code);
					return Stringify::uppercase($code);
				}
			}
		}
		
		return false;
	}

	/**
	 * Redirect request.
	 *
	 * @access public
	 * @param string $location
	 * @param int $status
	 * @return void
	 */
	public static function redirect(string $location, int $status = 301)
	{
		if ( $status ) {
			$message = Response::getMessage($status);
			header("Status: {$status} {$message}", true, $status);
		}
		header("Location: {$location}", true, $status);
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
		$schema = (self::isSsl()) ? 'https://' : 'http://';
		return "{$schema}{$url}";
	}

	/**
	 * Get current URL.
	 *
	 * @access public
	 * @param bool $escape, Escape query
	 * @return string
	 */
	public static function getCurrentUrl(bool $escape = false) : string
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
	public static function parseBaseUrl(string $url) : string
	{
		if ( empty($url) ) {
			return $url;
		}

		if ( ($url = Stringify::parseUrl($url)) ) {
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

		return $url;
	}

	/**
	 * Check basic authentication.
	 *
	 * @access public
	 * @return bool
	 */
	public static function isBasicAuth() : bool
	{
		return (self::getBasicAuthUser() && self::getBasicAuthPwd());
	}

	/**
	 * Get basic authentication user.
	 *
	 * @access public
	 * @return string
	 */
	public static function getBasicAuthUser() : string
	{
		return self::get('php-auth-user') ?: '';
	}

	/**
	 * Get basic authentication password.
	 *
	 * @access public
	 * @return string
	 */
	public static function getBasicAuthPwd() : string
	{
		return self::get('php-auth-pw') ?: '';
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

        } elseif ( TypeCheck::isFunction('apache-request-headers') ) {
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
			Stringify::match('/Bearer\s(\S+)/', $headers, $matches);
            $token = $matches[1] ?? false;
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
     * Get domain name from URL.
     *
     * @access public
     * @param string $url
     * @return string
     */
    public static function getDomain(?string $url = null) : string
    {
		$url = $url ?: self::getCurrentUrl(true);

		$pieces  = Stringify::parseUrl($url);
		$domain  = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];

		$pattern = '/(?P<domain>[a-z0-9][a-z0-9\\-]{1,63}\\.[a-z\\.]{2,6})$/i';
		Stringify::match($pattern, $domain, $domain, -1);

		return $domain ?: $url;
    }

    /**
     * Get HTTP referer.
     *
     * @access public
     * @return mixed
     */
    public static function getReferer()
    {
		return self::get('http-referer');
    }

    /**
     * Get server modules.
     *
     * @access public
     * @return array
     */
    public static function getModules() : array
    {
		$modules = [];
		if ( TypeCheck::isFunction('apache-get-modules') ) {
			$modules = apache_get_modules();
		}
		return $modules;
    }
}
