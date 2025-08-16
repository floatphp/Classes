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

use InvalidArgumentException;

/**
 * Advanced HTTP POST manipulation with enhanced security.
 */
final class Post
{
	/**
	 * Maximum allowed key length for security.
	 */
	private const MAX_KEY_LENGTH = 255;

	/**
	 * Get _POST value with enhanced validation and optional sanitization.
	 *
	 * @access public
	 * @param string|null $key The key to retrieve from $_POST
	 * @param bool $sanitize Whether to sanitize the output for XSS protection
	 * @return mixed Returns the sanitized value, null if not found, or entire $_POST array if key is null
	 * @throws InvalidArgumentException When key is invalid
	 */
	public static function get(?string $key = null, bool $sanitize = false) : mixed
	{
		if ( $key !== null ) {
			self::validateKey($key);
			if ( self::isSet($key) ) {
				$value = $_POST[$key];
				return $sanitize ? self::sanitize($value) : $value;
			}
			return null;
		}

		$data = self::isSet() ? $_POST : null;
		return $sanitize && $data !== null ? self::sanitizeArray($data) : $data;
	}

	/**
	 * Set _POST value with validation.
	 *
	 * @access public
	 * @param string $key The key to set in $_POST
	 * @param mixed $value The value to set
	 * @return void
	 * @throws InvalidArgumentException When key is invalid
	 */
	public static function set(string $key, mixed $value = null) : void
	{
		self::validateKey($key);
		$_POST[$key] = $value;
	}

	/**
	 * Check _POST value with enhanced validation.
	 *
	 * @access public
	 * @param string|null $key The key to check in $_POST
	 * @return bool Returns true if key exists and has a value, false otherwise
	 * @throws InvalidArgumentException When key is invalid
	 */
	public static function isSet(?string $key = null) : bool
	{
		if ( $key !== null ) {
			self::validateKey($key);
			return isset($_POST[$key]);
		}
		return isset($_POST) && !empty($_POST);
	}

	/**
	 * Unset _POST value with validation.
	 *
	 * @access public
	 * @param string|null $key The key to unset from $_POST, or null to clear all
	 * @return void
	 * @throws InvalidArgumentException When key is invalid
	 */
	public static function unset(?string $key = null) : void
	{
		if ( $key !== null ) {
			self::validateKey($key);
			unset($_POST[$key]);
		} else {
			$_POST = [];
		}
	}

	/**
	 * Validate key for security and format requirements.
	 *
	 * @access private
	 * @param string $key The key to validate
	 * @return void
	 * @throws InvalidArgumentException When key is invalid
	 */
	private static function validateKey(string $key) : void
	{
		// Check for empty key
		if ( trim($key) === '' ) {
			throw new InvalidArgumentException('Key cannot be empty or whitespace only');
		}

		// Check key length for security
		if ( strlen($key) > self::MAX_KEY_LENGTH ) {
			throw new InvalidArgumentException(
				sprintf('Key length cannot exceed %d characters', self::MAX_KEY_LENGTH)
			);
		}

		// Check for potentially malicious characters
		if ( preg_match('/[<>"\'\0\x1F]/', $key) ) {
			throw new InvalidArgumentException('Key contains invalid characters');
		}

		// Check for control characters and null bytes
		if ( filter_var($key, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH) !== $key ) {
			throw new InvalidArgumentException('Key contains control characters');
		}
	}

	/**
	 * Sanitize value for XSS protection.
	 *
	 * @access private
	 * @param mixed $value The value to sanitize
	 * @return mixed The sanitized value
	 */
	private static function sanitize(mixed $value) : mixed
	{
		if ( is_string($value) ) {
			// Remove null bytes and control characters
			$value = filter_var($value, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);

			// Basic XSS protection
			$value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

			return $value;
		}

		if ( is_array($value) ) {
			return self::sanitizeArray($value);
		}

		return $value;
	}

	/**
	 * Recursively sanitize array values.
	 *
	 * @access private
	 * @param array $array The array to sanitize
	 * @return array The sanitized array
	 */
	private static function sanitizeArray(array $array) : array
	{
		$sanitized = [];

		foreach ($array as $key => $value) {
			// Sanitize the key as well
			$sanitizedKey = is_string($key) ? self::sanitize($key) : $key;
			$sanitized[$sanitizedKey] = self::sanitize($value);
		}

		return $sanitized;
	}
}
