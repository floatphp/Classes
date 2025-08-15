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

/**
 * Advanced JSON manipulation.
 */
final class Json extends File
{
	/**
	 * Parse JSON file.
	 *
	 * @access public
	 * @param string $file
	 * @param bool $isArray
	 * @return mixed
	 * @throws \InvalidArgumentException When file is invalid
	 * @throws \RuntimeException When JSON parsing fails
	 */
	public static function parse(string $file, bool $isArray = false) : mixed
	{
		if ( empty($file) ) {
			throw new \InvalidArgumentException('File path must be a non-empty string');
		}

		$content = self::r($file);
		if ( $content === false || $content === null ) {
			throw new \RuntimeException("Unable to read file: {$file}");
		}

		return self::decode($content, $isArray);
	}

	/**
	 * Decode JSON.
	 *
	 * @access public
	 * @param string $value
	 * @param bool $isArray
	 * @return mixed
	 * @throws \InvalidArgumentException When input is invalid
	 * @throws \RuntimeException When JSON decoding fails
	 */
	public static function decode(string $value, bool $isArray = false) : mixed
	{
		// Input validation
		if ( empty($value) ) {
			throw new \InvalidArgumentException('JSON string cannot be empty');
		}

		// Security
		$maxDepth = 512;
		$result = json_decode($value, $isArray, $maxDepth);

		// Check for errors
		$error = self::lastError();
		if ( $error !== JSON_ERROR_NONE ) {
			$errorMessage = self::getError($error);
			throw new \RuntimeException("JSON decode error: {$errorMessage}");
		}

		return $result;
	}

	/**
	 * Encode JSON without flags.
	 *
	 * @access public
	 * @param mixed $value
	 * @return mixed
	 * @throws \InvalidArgumentException When value cannot be encoded
	 * @throws \RuntimeException When JSON encoding fails
	 */
	public static function encode($value) : mixed
	{
		return self::format($value, 0);
	}

	/**
	 * Encode JSON using flags.
	 *
	 * [SLASHES : 64].
	 * [PRETTY  : 128].
	 * [UNICODE : 256].
	 *
	 * @access public
	 * @param mixed $value
	 * @param int $flags
	 * @param int $depth
	 * @return mixed
	 * @throws \InvalidArgumentException When parameters are invalid
	 * @throws \RuntimeException When JSON encoding fails
	 */
	public static function format($value, int $flags = 64 | 256, int $depth = 512) : mixed
	{
		// Validate depth parameter
		if ( $depth < 1 || $depth > 2147483647 ) {
			throw new \InvalidArgumentException('Depth must be between 1 and 2147483647');
		}

		// Validate value is encodable
		if ( is_resource($value) ) {
			throw new \InvalidArgumentException('Cannot encode resource type');
		}

		// Encode with error handling
		$result = json_encode($value, $flags, $depth);

		// Check for errors
		$error = self::lastError();
		if ( $error !== JSON_ERROR_NONE ) {
			$errorMessage = self::getError($error);
			throw new \RuntimeException("JSON encode error: {$errorMessage}");
		}

		return $result;
	}

	/**
	 * Validate JSON string.
	 *
	 * @access public
	 * @param string $value
	 * @return bool
	 */
	public static function isValid(string $value) : bool
	{
		if ( empty($value) ) {
			return false;
		}

		json_decode($value);
		return self::lastError() === JSON_ERROR_NONE;
	}

	/**
	 * Get JSON last error.
	 *
	 * @access public
	 * @return int
	 */
	public static function lastError() : int
	{
		return json_last_error();
	}

	/**
	 * Get human-readable JSON error message.
	 *
	 * @access private
	 * @param int $error
	 * @return string
	 */
	private static function getError(int $error) : string
	{
		switch ($error) {
			case JSON_ERROR_NONE:
				return 'No error';
			case JSON_ERROR_DEPTH:
				return 'Maximum stack depth exceeded';
			case JSON_ERROR_STATE_MISMATCH:
				return 'State mismatch (invalid or malformed JSON)';
			case JSON_ERROR_CTRL_CHAR:
				return 'Control character error';
			case JSON_ERROR_SYNTAX:
				return 'Syntax error, malformed JSON';
			case JSON_ERROR_UTF8:
				return 'Malformed UTF-8 characters';
			case JSON_ERROR_RECURSION:
				return 'One or more recursive references in the value to be encoded';
			case JSON_ERROR_INF_OR_NAN:
				return 'One or more NAN or INF values in the value to be encoded';
			case JSON_ERROR_UNSUPPORTED_TYPE:
				return 'A value of a type that cannot be encoded was given';
			case JSON_ERROR_INVALID_PROPERTY_NAME:
				return 'A property name that cannot be encoded was given';
			case JSON_ERROR_UTF16:
				return 'Malformed UTF-16 characters';
			default:
				return "Unknown JSON error (code: {$error})";
		}
	}
}
