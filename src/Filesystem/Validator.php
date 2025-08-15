<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file is a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

use FloatPHP\Classes\Http\{Server, Stream};
use FloatPHP\Classes\Server\System;

/**
 * Advanced data validator.
 */
class Validator
{
	/**
	 * Validate email.
	 *
	 * [FILTER_VALIDATE_EMAIL : 274].
	 *
	 * @access public
	 * @param string $email
	 * @return bool
	 */
	public static function isEmail(string $email) : bool
	{
		return (bool)Stringify::filter($email, null, 274);
	}

	/**
	 * Validate URL.
	 *
	 * [FILTER_VALIDATE_URL : 273].
	 *
	 * @access public
	 * @param string $url
	 * @return bool
	 */
	public static function isUrl(string $url) : bool
	{
		return (bool)Stringify::filter($url, null, 273);
	}

	/**
	 * Validate date.
	 *
	 * @access public
	 * @param mixed $date
	 * @param bool $time
	 * @return bool
	 */
	public static function isDate($date, bool $time = false) : bool
	{
		// Object
		if ( TypeCheck::isObject($date) ) {
			return $date instanceof \DateTime;
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
	public static function isIp(string $ip) : bool
	{
		$pattern = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';
		$match = Stringify::match($pattern, $ip, $matches);
		return $match || self::isIpV6($ip);
	}

	/**
	 * Validate MAC.
	 *
	 * @access public
	 * @param string $address
	 * @return bool
	 */
	public static function isMac(string $address) : bool
	{
		$pattern = "/^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/i";
		return Stringify::match($pattern, $address, $matches);
	}

	/**
	 * Check stream (path).
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function isStream(string $path) : bool
	{
		return Stream::isValid($path);
	}

	/**
	 * Validate file mime type.
	 *
	 * @access public
	 * @param string $file
	 * @param array $types
	 * @return bool
	 */
	public static function isMime(string $file, ?array $types = null) : bool
	{
		if ( empty($file) || !File::exists($file) ) {
			return false;
		}

		// Get MIME type using multiple methods for reliability
		$mime = File::getMimeType($file);

		// Fallback to PHP's mime_content_type if available
		if ( $mime === 'undefined' && TypeCheck::isFunction('mime_content_type') ) {
			$mime = @mime_content_type($file);
		}

		if ( $types === null || empty($types) ) {
			// If no specific types are required, just check if we got a valid MIME type
			return !empty($mime) && $mime !== 'undefined';
		}

		// Check against allowed MIME types
		foreach ($types as $ext => $allowedMime) {
			if ( $mime === $allowedMime ) {
				return true;
			}
		}

		return false;
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
		return System::getIni($name) == $value;
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
	public static function isIpV6(string $ip) : bool
	{
		$ip = self::uncompressIpV6($ip);
		list($ipv6, $ipv4) = self::splitIpV6($ip);

		$ipv6 = explode(':', $ipv6);
		$ipv4 = explode('.', $ipv4);

		if ( count($ipv6) === 8 && count($ipv4) === 1 || count($ipv6) === 6 && count($ipv4) === 4 ) {
			foreach ($ipv6 as $part) {

				if ( $part === '' ) {
					return false;
				}

				if ( strlen($part) > 4 ) {
					return false;
				}

				$part = ltrim($part, '0');
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
					$value = (int)$part;
					if ( (string)$value !== $part || $value < 0 || $value > 0xFF ) {
						return false;
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Validate cookie value.
	 * 
	 * @access public
	 * @param string $valu
	 * @param int $length
	 * @return bool
	 */
	public static function isCookieValue(string $value, int $length = 4096) : bool
	{
		// Check length
		if ( strlen($value) > $length ) {
			return false;
		}

		// Check for control characters (0x00-0x1F and 0x7F)
		if ( Stringify::match('/[\x00-\x1F\x7F]/', $value) ) {
			return false;
		}

		return true;
	}

	/**
	 * Validate cookie name.
	 * 
	 * @access public
	 * @param string $name
	 * @param int $length
	 * @return bool
	 */
	public static function isCookieName(string $name, int $length = 255) : bool
	{
		// Check length
		if ( strlen($name) > $length || empty($name) ) {
			return false;
		}

		// Check for invalid characters (RFC 6265)
		$invalidChars = ['(', ')', '<', '>', '@', ',', ';', ':', '\\', '"', '/', '[', ']', '?', '=', '{', '}', ' ', "\t"];

		foreach ($invalidChars as $char) {
			if ( Stringify::contains($name, $char) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Uncompresses IPv6 address.
	 *
	 * @access public
	 * @return string
	 */
	public static function uncompressIpV6(string $ip) : string
	{
		if ( Stringify::subCount($ip, '::') !== 1 ) {
			return $ip;
		}

		list($ip1, $ip2) = explode('::', $ip);
		$c1 = ($ip1 === '') ? -1 : Stringify::subCount($ip1, ':');
		$c2 = ($ip2 === '') ? -1 : Stringify::subCount($ip2, ':');

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
	 * @access public
	 * @param string $ip
	 * @return array
	 */
	public static function splitIpV6(string $ip) : array
	{
		if ( strpos($ip, '.') !== false ) {
			$pos = (int)strrpos($ip, ':');
			$ipv6 = substr($ip, 0, $pos);
			$ipv4 = substr($ip, $pos + 1);
			return [$ipv6, $ipv4];
		}
		return [$ip, ''];
	}

	/**
	 * Check if two IP addresses are in the same subnet.
	 *
	 * @access public
	 * @param string $ip1
	 * @param string $ip2
	 * @param int $cidr CIDR subnet mask (default: 24 for /24 subnet)
	 * @return bool
	 */
	public static function isSameSubnet(string $ip1, string $ip2, int $cidr = 24) : bool
	{
		// For exact match (most secure)
		if ( $ip1 === $ip2 ) {
			return true;
		}

		// For IPv4 subnet checking
		if (
			Stringify::filter($ip1, 1048576, 275) &&
			Stringify::filter($ip2, 1048576, 275)
		) {

			$mask = -1 << (32 - $cidr);
			return (ip2long($ip1) & $mask) === (ip2long($ip2) & $mask);
		}

		// For IPv6 or other cases, require exact match
		return false;
	}

	/**
	 * Validate and sanitize file path against directory traversal attacks.
	 *
	 * @access public
	 * @param string $path
	 * @param bool $throwException
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public static function isPath(string $path, bool $throwException = false) : string
	{
		// Normalize path
		$path = Stringify::formatPath($path);

		// Check for directory traversal patterns
		$dangerous = ['../', '..\/', '../', '..\\'];
		foreach ($dangerous as $pattern) {
			if ( Stringify::contains($path, $pattern) ) {
				if ( $throwException ) {
					throw new \InvalidArgumentException("Path contains directory traversal: {$path}");
				}
				return '';
			}
		}

		// Check for null bytes (path truncation attack)
		if ( Stringify::contains($path, "\0") ) {
			if ( $throwException ) {
				throw new \InvalidArgumentException("Path contains null byte: {$path}");
			}
			return '';
		}

		return $path;
	}
}
