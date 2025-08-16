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
		if ( trim($email) === '' ) {
			return false;
		}
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
		if ( trim($url) === '' ) {
			return false;
		}
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
		if ( trim($ip) === '' ) {
			return false;
		}
		return (bool)Stringify::filter($ip, 'ip');
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
		if ( trim($ip) === '' ) {
			return false;
		}
		return (bool)Stringify::filter($ip, 'ipv6');
	}

	/**
	 * Validate MAC.
	 *
	 * @access public
	 * @param string $mac
	 * @return bool
	 */
	public static function isMac(string $mac) : bool
	{
		if ( trim($mac) === '' || strlen($mac) !== 17 ) {
			return false;
		}
		return (bool)Stringify::filter($mac, 'mac');
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
		if ( trim($path) === '' ) {
			return false;
		}
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
		if ( $mime === 'undefined' && TypeCheck::isFunction('mime-content-type') ) {
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
		if ( trim($module) === '' ) {
			return false;
		}
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
	 * Check if two IP addresses are in the same subnet.
	 *
	 * @access public
	 * @param string $ip1
	 * @param string $ip2
	 * @param int $cidr CIDR subnet mask
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
}
