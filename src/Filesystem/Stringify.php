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
 * Advanced I/O helper and string manipulation.
 */
final class Stringify
{
	private const SPECIALCHARS = [
		"Š" => "S",
		"š" => "s",
		"Ž" => "Z",
		"ž" => "z",
		"À" => "A",
		"Á" => "A",
		"Â" => "A",
		"Ã" => "A",
		"Ä" => "A",
		"Å" => "A",
		"Æ" => "A",
		"Ç" => "C",
		"È" => "E",
		"É" => "E",
		"Ê" => "E",
		"Ë" => "E",
		"Ì" => "I",
		"Í" => "I",
		"Î" => "I",
		"Ï" => "I",
		"Ñ" => "N",
		"Ò" => "O",
		"Ó" => "O",
		"Ô" => "O",
		"Õ" => "O",
		"Ö" => "O",
		"Ø" => "O",
		"Ù" => "U",
		"Ú" => "U",
		"Û" => "U",
		"Ü" => "U",
		"Ý" => "Y",
		"Þ" => "B",
		"ß" => "Ss",
		"à" => "a",
		"á" => "a",
		"â" => "a",
		"ã" => "a",
		"ä" => "a",
		"å" => "a",
		"æ" => "a",
		"ç" => "c",
		"è" => "e",
		"é" => "e",
		"ê" => "e",
		"ë" => "e",
		"ì" => "i",
		"í" => "i",
		"î" => "i",
		"ï" => "i",
		"ð" => "o",
		"ñ" => "n",
		"ò" => "o",
		"ó" => "o",
		"ô" => "o",
		"õ" => "o",
		"ö" => "o",
		"ø" => "o",
		"ù" => "u",
		"ú" => "u",
		"û" => "u",
		"ý" => "y",
		"þ" => "b",
		"ÿ" => "y"
	];

	/**
	 * Search replace string(s).
	 * 
	 * @access public
	 * @param string|array $search
	 * @param string|array $replace
	 * @param string|array $subject
	 * @param int|null $count
	 * @return string|array
	 */
	public static function replace(string|array $search, string|array $replace, string|array $subject, ?int &$count = null) : string|array
	{
		return str_replace($search, $replace, $subject, $count);
	}

	/**
	 * Search replace substring(s).
	 *
	 * @access public
	 * @param string $string
	 * @param string $replacement
	 * @param int $offset
	 * @param int|null $length
	 * @return string
	 */
	public static function subReplace(string $string, string $replacement, int $offset = 0, ?int $length = null) : string
	{
		return substr_replace($string, $replacement, $offset, $length);
	}

	/**
	 * Count substring(s).
	 *
	 * @access public
	 * @param string $haystack
	 * @param string $needle
	 * @param int $offset
	 * @param int $length
	 * @return int
	 */
	public static function subCount(string $haystack, string $needle, int $offset = 0, ?int $length = null) : int
	{
		return substr_count($haystack, $needle, $offset, $length);
	}

	/**
	 * Search replace string(s) using array.
	 * 
	 * @access public
	 * @param array $replace
	 * @param string $subject
	 * @return string
	 */
	public static function replaceArray(array $replace, string $subject) : string
	{
		foreach ($replace as $key => $value) {
			$subject = self::replace($key, $value, $subject);
		}
		return $subject;
	}

	/**
	 * Search replace string(s) using regex.
	 *
	 * @access public
	 * @param string|array $pattern
	 * @param string|array $replacement
	 * @param string|array $subject
	 * @param int $limit
	 * @param int|null $count
	 * @return string|array|null
	 * @throws \InvalidArgumentException When regex pattern is invalid
	 * @throws \RuntimeException When regex execution fails
	 */
	public static function replaceRegex(string|array $pattern, string|array $replacement, string|array $subject, int $limit = -1, ?int &$count = null) : string|array|null
	{
		// Input validation
		if ( is_string($pattern) && empty($pattern) ) {
			throw new \InvalidArgumentException('Regex pattern cannot be empty');
		}

		$result = preg_replace($pattern, $replacement, $subject, $limit, $count);

		// Check for regex errors
		if ( $result === null ) {
			$error = preg_last_error();
			$errorMessage = self::getPregError($error);
			throw new \RuntimeException("Regex execution failed: {$errorMessage}");
		}

		return $result;
	}

	/**
	 * Search replace string(s) using regex callback.
	 *
	 * @access public
	 * @param string|array $pattern
	 * @param callable $callback
	 * @param string|array $subject
	 * @param int $limit
	 * @param int|null $count
	 * @return string|array|null
	 * @throws \InvalidArgumentException When pattern or callback is invalid
	 * @throws \RuntimeException When regex execution fails
	 */
	public static function replaceRegexCb(string|array $pattern, callable $callback, string|array $subject, int $limit = -1, ?int &$count = null) : string|array|null
	{
		// Input validation
		if ( is_string($pattern) && empty($pattern) ) {
			throw new \InvalidArgumentException('Regex pattern cannot be empty');
		}

		if ( !is_callable($callback) ) {
			throw new \InvalidArgumentException('Callback must be callable');
		}

		$result = preg_replace_callback($pattern, $callback, $subject, $limit, $count);

		// Check for regex errors
		if ( $result === null ) {
			$error = preg_last_error();
			$errorMessage = self::getPregError($error);
			throw new \RuntimeException("Regex callback execution failed: {$errorMessage}");
		}

		return $result;
	}

	/**
	 * Remove string from other string.
	 *
	 * @access public
	 * @param string|array $string
	 * @param string|array $subject
	 * @return string|array
	 */
	public static function remove(string|array $string, string|array $subject) : string|array
	{
		return self::replace($string, '', $subject);
	}

	/**
	 * Remove sub string.
	 * 
	 * @access public
	 * @param string $string
	 * @param int $offset
	 * @param int|null $length
	 * @return string
	 */
	public static function subRemove(string $string, int $offset = 0, ?int $length = null) : string
	{
		if ( $length === null ) {
			$length = strlen($string);
		}
		return self::subReplace($string, '', $offset, $length);
	}

	/**
	 * Remove string from other string using regex.
	 * 
	 * @access public
	 * @param string $regex
	 * @param string $subject
	 * @return string
	 */
	public static function removeRegex(string $regex, string $subject) : string
	{
		return (string)self::replaceRegex($regex, '', $subject);
	}

	/**
	 * Repeat string.
	 * 
	 * @access public
	 * @param string $string
	 * @param int $times
	 * @return string
	 */
	public static function repeat(string $string, int $times = 0) : string
	{
		return str_repeat($string, $times);
	}

	/**
	 * Lowercase string.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function lowercase(string $string) : string
	{
		return strtolower($string);
	}

	/**
	 * Uppercase string.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function uppercase(string $string) : string
	{
		return strtoupper($string);
	}

	/**
	 * Capitalize string.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function capitalize(string $string) : string
	{
		return ucfirst(self::lowercase($string));
	}

	/**
	 * Camelcase string.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function camelcase(string $string) : string
	{
		$string = explode('-', self::slugify($string));
		$string = Arrayify::values(
			Arrayify::filter($string)
		);
		$first = $string[0] ?? '';
		$string = Arrayify::map(function ($val) use ($first) {
			if ( $val === $first ) {
				return $val;
			}
			return self::capitalize($val);
		}, $string);
		return self::undash(
			implode('', $string)
		);
	}

	/**
	 * Slugify string.
	 * 
	 * Converts a string to a URL-friendly slug by:
	 * 1. Replacing non-alphanumeric characters with hyphens
	 * 2. Transliterating special characters 
	 * 3. Converting to ASCII
	 * 4. Removing unwanted characters
	 * 5. Converting to lowercase
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 * @throws \RuntimeException When transliteration fails
	 */
	public static function slugify(string $string) : string
	{
		try {
			// Replace non letter or digits by -
			$slug = self::replaceRegex('~[^\pL\d]+~u', '-', (string)$string);

			// Transliterate special characters
			$slug = strtr($slug, static::SPECIALCHARS);
			$slug = self::encode($slug, 'ASCII//TRANSLIT//IGNORE');

			// Remove unwanted characters
			$slug = self::replaceRegex('~[^-\w]+~', '', $slug);

			// Trim hyphens
			$slug = trim($slug, '-');

			// Remove duplicate hyphens
			$slug = self::replaceRegex('~-+~', '-', $slug);

			// Convert to lowercase
			return strtolower($slug);

		} catch (\Exception $e) {
			// Fallback: basic slugification
			$slug = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $string);
			$slug = preg_replace('/-+/', '-', $slug);
			return strtolower(trim($slug, '-'));
		}
	}

	/**
	 * Get special chars.
	 * 
	 * @access public
	 * @return array
	 * @throws \RuntimeException When special chars file cannot be loaded
	 */
	public static function getSpecialChars() : array
	{
		try {
			$file = dirname(__FILE__) . '/bin/special.json';

			// Validate file exists and is readable
			if ( !file_exists($file) || !is_readable($file) ) {
				throw new \RuntimeException("Special characters file not found or not readable: {$file}");
			}

			$result = Json::parse(file: $file, isArray: true);

			if ( !is_array($result) ) {
				throw new \RuntimeException('Special characters file must contain a valid JSON array');
			}

			return $result;

		} catch (\Exception $e) {
			// Fallback to basic special characters if file loading fails
			return [
				'à' => 'a',
				'á' => 'a',
				'â' => 'a',
				'ã' => 'a',
				'ä' => 'a',
				'å' => 'a',
				'è' => 'e',
				'é' => 'e',
				'ê' => 'e',
				'ë' => 'e',
				'ì' => 'i',
				'í' => 'i',
				'î' => 'i',
				'ï' => 'i',
				'ò' => 'o',
				'ó' => 'o',
				'ô' => 'o',
				'õ' => 'o',
				'ö' => 'o',
				'ù' => 'u',
				'ú' => 'u',
				'û' => 'u',
				'ü' => 'u',
				'ñ' => 'n',
				'ç' => 'c'
			];
		}
	}

	/**
	 * Search string.
	 *
	 * @access public
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	public static function contains(string $haystack, string $needle) : bool
	{
		return str_contains($haystack, $needle);
	}

	/**
	 * Split string.
	 *
	 * @access public
	 * @param string $string
	 * @param array $args [regex, limit, flags, length]
	 * @return mixed
	 * @throws \InvalidArgumentException When parameters are invalid
	 * @throws \RuntimeException When regex split fails
	 */
	public static function split(string $string, array $args = []) : mixed
	{
		if ( isset($args['regex']) ) {
			// Input validation for regex split
			if ( empty($args['regex']) ) {
				throw new \InvalidArgumentException('Regex pattern cannot be empty');
			}

			$limit = $args['limit'] ?? -1;
			$flags = $args['flags'] ?? 0;

			// Validate parameters
			if ( !is_int($limit) ) {
				throw new \InvalidArgumentException('Limit must be an integer');
			}

			if ( !is_int($flags) || $flags < 0 ) {
				throw new \InvalidArgumentException('Flags must be a non-negative integer');
			}

			$result = preg_split($args['regex'], $string, $limit, $flags);

			// Check for regex errors
			if ( $result === false ) {
				$error = preg_last_error();
				$errorMessage = self::getPregError($error);
				throw new \RuntimeException("Regex split failed: {$errorMessage}");
			}

			return $result;
		}

		// Regular string split
		$length = $args['length'] ?? 1;
		if ( !is_int($length) || $length < 1 ) {
			throw new \InvalidArgumentException('Split length must be a positive integer');
		}

		return str_split($string, $length);
	}

	/**
	 * Split string to smaller chunks.
	 *
	 * @access public
	 * @param string $string
	 * @param int $length
	 * @param string $sep, separator
	 * @return string
	 */
	public static function chunk(string $string, int $length = 76, string $sep = "\r\n") : string
	{
		return chunk_split($string, $length, $sep);
	}

	/**
	 * Encode string | Default encode string to UTF-8.
	 *
	 * @access public
	 * @param string $string
	 * @param string $from
	 * @param string $to
	 * @return string
	 * @throws \InvalidArgumentException When encoding parameters are invalid
	 * @throws \RuntimeException When encoding conversion fails
	 */
	public static function encode(string $string, string $from = 'ISO-8859-1', string $to = 'UTF-8') : string
	{
		// Input validation
		if ( empty($string) ) {
			return $string;
		}

		if ( empty($from) || empty($to) ) {
			throw new \InvalidArgumentException('Encoding parameters cannot be empty');
		}

		// Validate encoding names
		if ( !self::isValidEncoding($from) || !self::isValidEncoding($to) ) {
			throw new \InvalidArgumentException("Invalid encoding specified: from '{$from}' to '{$to}'");
		}

		if ( self::getEncoding($string, $to) !== self::uppercase($to) ) {
			$encoded = @iconv($from, $to, $string);
			if ( $encoded === false ) {
				throw new \RuntimeException("Failed to convert encoding from '{$from}' to '{$to}'");
			}
			if ( $encoded !== false ) {
				$string = $encoded;
			}
		}
		return $string;
	}

	/**
	 * Detect encoding
	 *
	 * @access public
	 * @param string $string
	 * @param mixed $encodings
	 * @return mixed
	 */
	public static function getEncoding(string $string, $encodings = null) : mixed
	{
		if ( TypeCheck::isFunction('mb_detect_encoding') ) {
			return mb_detect_encoding($string, $encodings, true);
		}
		return false;
	}

	/**
	 * Check whether string is UTF8.
	 *
	 * @access public
	 * @param string $string
	 * @return bool
	 */
	public static function isUtf8(string $string) : bool
	{
		$length = strlen($string);
		for ($i = 0; $i < $length; $i++) {
			$c = ord($string[$i]);
			if ( $c < 0x80 ) {
				$n = 0; // 0bbbbbbb
			} elseif ( ($c & 0xE0) == 0xC0 ) {
				$n = 1; // 110bbbbb
			} elseif ( ($c & 0xF0) == 0xE0 ) {
				$n = 2; // 1110bbbb
			} elseif ( ($c & 0xF8) == 0xF0 ) {
				$n = 3; // 11110bbb
			} elseif ( ($c & 0xFC) == 0xF8 ) {
				$n = 4; // 111110bb
			} elseif ( ($c & 0xFE) == 0xFC ) {
				$n = 5; // 1111110b
			} else {
				return false;
			}
			for ($j = 0; $j < $n; $j++) {
				if ( (++$i == $length) || ((ord($string[$i]) & 0xC0) != 0x80) ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Format path.
	 *
	 * @access public
	 * @param string $path
	 * @param bool $untrailing
	 * @return string
	 */
	public static function formatPath(string $path, bool $untrailing = false) : string
	{
		$prefix = '';

		// Stream format
		if ( Validator::isStream($path) ) {
			list($prefix, $path) = explode('://', $path, 2);
			$prefix .= '://';
		}

		// Paths format
		$path = self::replace('\\', '/', $path);

		// Multiple slashes format
		$path = self::replaceRegex('|(?<=.)/+|', '/', $path);

		// Windows format
		if ( substr($path, 1, 1) === ':' ) {
			$path = ucfirst($path);
		}

		// Untrailing slash
		if ( $untrailing ) {
			return self::untrailingSlash("{$prefix}{$path}");
		}

		return "{$prefix}{$path}";
	}

	/**
	 * Format whitespaces,
	 * Including breaks.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function formatSpace(string $string) : string
	{
		$string = trim($string);
		$string = self::replace("\r", "\n", $string);
		$string = self::replaceRegex(['/\n+/', '/[ \t]+/'], ["\n", ' '], $string);
		return $string;
	}

	/**
	 * Format key.
	 *
	 * @access public
	 * @param string $key
	 * @return string
	 */
	public static function formatKey(string $key) : string
	{
		$key = self::lowercase($key);
		return (string)self::replaceRegex('/[^a-z0-9_\-]/', '', $key);
	}

	/**
	 * Remove slashes from value,
	 * Accept string and array.
	 * 
	 * @access public
	 * @param mixed $value
	 * @return mixed
	 */
	public static function unSlash($value) : mixed
	{
		return self::replaceRegex('/\//', '', $value);
	}

	/**
	 * Add slashes to value,
	 * Accept string and array.
	 * 
	 * @access public
	 * @param mixed $value
	 * @return mixed
	 */
	public static function slash($value) : mixed
	{
		if ( TypeCheck::isString($value) ) {
			return '/' . self::unSlash($value);
		}
		if ( TypeCheck::isArray($value) ) {
			foreach ($value as $key => $val) {
				$val = (string)$val;
				$value[$key] = '/' . self::unSlash($val);
			}
		}
		return $value;
	}

	/**
	 * Remove trailing slashes and backslashes if exist.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function untrailingSlash(string $string) : string
	{
		return rtrim($string, '/\\');
	}

	/**
	 * Append trailing slashes.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function trailingSlash(string $string) : string
	{
		return self::untrailingSlash($string) . '/';
	}

	/**
	 * Strip slashes in quotes or single quotes,
	 * Removes double backslashs.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function stripSlash(string $string) : string
	{
		return stripslashes($string);
	}

	/**
	 * Strip slashes in quotes or single quotes,
	 * Removes double backslashs.
	 * (array, object, scalar).
	 *
	 * @access public
	 * @param mixed $value
	 * @return mixed
	 */
	public static function deepStripSlash($value) : mixed
	{
		return self::deepMap($value, function ($string) {
			return TypeCheck::isString($string)
				? self::stripSlash($string) : $string;
		});
	}

	/**
	 * Strip HTML tags in string,
	 * Including script and style.
	 *
	 * @access public
	 * @param string $string
	 * @param bool $unbreak
	 * @return string
	 */
	public static function stripTag(string $string, bool $unbreak = false) : string
	{
		$string = self::replaceRegex('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
		$string = strip_tags($string);
		if ( $unbreak ) {
			$string = self::replaceRegex('/[\r\n\t ]+/', ' ', $string);
		}
		return trim($string);
	}

	/**
	 * Strip numbers in string,
	 * Using custom replace string.
	 *
	 * @access public
	 * @param string $string
	 * @param string $replace
	 * @return string
	 */
	public static function stripNumber(string $string, string $replace = '') : string
	{
		return (string)self::replaceRegex('/[0-9]+/', $replace, $string);
	}

	/**
	 * Strip special characters in string,
	 * Using custom replace string.
	 *
	 * @access public
	 * @param string $string
	 * @param string $replace
	 * @return string
	 */
	public static function stripChar(string $string, string $replace = '') : string
	{
		return (string)self::replaceRegex('/[^a-zA-Z0-9\s]/', $replace, $string);
	}

	/**
	 * Strip spaces in string,
	 * Using custom replace string.
	 *
	 * @access public
	 * @param string $string
	 * @param string $replace
	 * @return string
	 */
	public static function stripSpace(string $string, string $replace = '') : string
	{
		return (string)self::replaceRegex('/\s+/', $replace, trim($string));
	}

	/**
	 * Strip break in string,
	 * Using custom replace string.
	 *
	 * @access public
	 * @param string $string
	 * @param string $replace
	 * @return string
	 */
	public static function stripBreak(string $string, string $replace = '') : string
	{
		return (string)self::replaceRegex('/\r|\n/', $replace, $string);
	}

	/**
	 * Unserialize serialized value.
	 *
	 * @access public
	 * @param string $value
	 * @return mixed
	 */
	public static function unserialize(string $value) : mixed
	{
		if ( self::isSerialized($value) ) {
			return @unserialize(trim($value));
		}
		return $value;
	}

	/**
	 * Serialize value if not serialized.
	 *
	 * @access public
	 * @param mixed $value
	 * @return mixed
	 */
	public static function serialize($value) : mixed
	{
		if ( TypeCheck::isArray($value) || TypeCheck::isObject($value) ) {
			return serialize($value);
		}
		if ( TypeCheck::isInt($value) || TypeCheck::isFloat($value) ) {
			return serialize((string)$value);
		}
		if ( self::isSerialized($value, false) ) {
			return serialize($value);
		}
		return $value;
	}

	/**
	 * Check whether value is serialized.
	 * 
	 * @access public
	 * @param mixed $value
	 * @param bool $strict
	 * @return bool
	 */
	public static function isSerialized($value, bool $strict = true) : bool
	{
		// Basic type validation
		if ( !TypeCheck::isString($value) ) {
			return false;
		}

		$value = trim($value);

		// Handle null serialization
		if ( $value === 'N;' ) {
			return true;
		}

		// Minimum length check
		if ( strlen($value) < 4 ) {
			return false;
		}

		// Format validation
		if ( !self::validateSerializedFormat($value, $strict) ) {
			return false;
		}

		// Token-specific validation
		return self::validateSerializedToken($value[0], $value, $strict);
	}

	/**
	 * Match string using regex.
	 *
	 * @access public
	 * @param string $regex
	 * @param string $string
	 * @param mixed $matches
	 * @param int $flags
	 * @param int $offset
	 * @return bool
	 * @throws \InvalidArgumentException When regex or parameters are invalid
	 * @throws \RuntimeException When regex execution fails
	 */
	public static function match(string $regex, string $string, &$matches = null, int $flags = 0, int $offset = 0) : bool
	{
		// Input validation
		if ( empty($regex) ) {
			throw new \InvalidArgumentException('Regex pattern cannot be empty');
		}

		if ( $offset < 0 || $offset > strlen($string) ) {
			throw new \InvalidArgumentException('Offset must be within string bounds');
		}

		$shift = ($flags === -1) ? true : false;
		$flags = ($flags !== -1) ? $flags : 0;

		$matched = preg_match($regex, $string, $matches, $flags, $offset);

		// Check for regex errors
		if ( $matched === false ) {
			$error = preg_last_error();
			$errorMessage = self::getPregError($error);
			throw new \RuntimeException("Regex match failed: {$errorMessage}");
		}

		if ( $shift && $matches ) {
			$matches = $matches[0] ?? [];
		}

		return (bool)$matched;
	}

	/**
	 * Match all strings using regex (g).
	 *
	 * @access public
	 * @param string $regex
	 * @param string $string
	 * @param mixed $matches
	 * @param int $flags
	 * @param int $offset
	 * @return bool
	 */
	public static function matchAll(string $regex, string $string, &$matches, int $flags = 0, int $offset = 0) : bool
	{
		$shift = ($flags === -1) ? true : false;
		$flags = ($flags !== -1) ? $flags : 0;

		$matched = (bool)preg_match_all($regex, $string, $matches, $flags, $offset);

		if ( $shift && $matches ) {
			$matches = $matches[0] ?? [];
		}

		return (bool)$matched;
	}

	/**
	 * Shuffle string.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function shuffle(string $string) : string
	{
		return str_shuffle($string);
	}

	/**
	 * Count chars in string.
	 * 
	 * @access public
	 * @param string $string
	 * @param int $mode
	 * @return mixed
	 */
	public static function count(string $string, int $mode = 0) : mixed
	{
		return count_chars($string, $mode);
	}

	/**
	 * Limit string (Without breaking words).
	 * 
	 * @access public
	 * @param string $string
	 * @param int $length Maximum length
	 * @param int $offset Starting position
	 * @param string|null $suffix Suffix for truncated strings
	 * @return string
	 * @throws \InvalidArgumentException When parameters are invalid
	 */
	public static function limit(string $string, int $length = 128, int $offset = 0, ?string $suffix = '...') : string
	{
		// Input validation
		if ( $length < 0 ) {
			throw new \InvalidArgumentException('Length must be non-negative');
		}

		if ( $offset < 0 || $offset > strlen($string) ) {
			throw new \InvalidArgumentException('Offset must be within string bounds');
		}

		if ( $length === 0 ) {
			return '';
		}

		$limit = $string;

		try {
			$words = self::split($string, [
				'regex' => '/([\s\n\r]+)/u',
				'limit' => 0,
				'flags' => 2 // PREG_SPLIT_DELIM_CAPTURE
			]);

			if ( ($count = count($words)) ) {
				$strlen = 0;
				$last = $offset;
				for (; $last < $count; ++$last) {
					$strlen += strlen($words[$last]);
					if ( $strlen > $length ) {
						break;
					}
				}

				if ( class_exists('Arrayify') && method_exists('Arrayify', 'slice') ) {
					$limit = implode(Arrayify::slice($words, $offset, $last));
				} else {
					// Fallback if Arrayify is not available
					$limit = implode(array_slice($words, $offset, $last - $offset));
				}
			}

			if ( empty($limit) ) {
				$limit = substr($string, $offset, $length);
			}

			if ( strlen($string) > $length && $suffix !== null ) {
				$limit .= " {$suffix}";
			}

		} catch (\Exception $e) {
			// Fallback to simple substr if word-boundary logic fails
			$limit = substr($string, $offset, $length);
			if ( strlen($string) > $length && $suffix !== null ) {
				$limit .= $suffix;
			}
		}

		return trim($limit);
	}

	/**
	 * Filter string (Validation toolkit).
	 *
	 * Predefined filters:
	 * - 'email': Sanitize email address
	 * - 'name': Default sanitization without encoding quotes
	 * - 'text': Remove low ASCII characters
	 * - 'url': Sanitize URL
	 * - 'ip': Validate IP address
	 * - 'ipv6': Validate IPv6 address
	 * - 'mac': Validate MAC address
	 * 
	 * Default filter constant: FILTER_DEFAULT (516)
	 *
	 * @access public
	 * @param mixed $value
	 * @param string|null $type
	 * @param int $filter Filter constant (default: 516 = FILTER_DEFAULT)
	 * @param mixed $options Filter options
	 * @return mixed
	 * @throws \InvalidArgumentException When filter type is invalid
	 */
	public static function filter($value, ?string $type = 'name', int $filter = 516, $options = 0) : mixed
	{
		// Input validation
		if ( $type !== null && !is_string($type) ) {
			throw new \InvalidArgumentException('Filter type must be a string or null');
		}

		$type = self::lowercase((string)$type);

		return match ($type) {
			'email' => filter_var($value, FILTER_SANITIZE_EMAIL),
			'name'  => filter_var($value, FILTER_DEFAULT, FILTER_FLAG_NO_ENCODE_QUOTES),
			'text'  => filter_var($value, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW),
			'url'   => filter_var($value, FILTER_SANITIZE_URL),
			'ip'    => filter_var($value, FILTER_VALIDATE_IP),
			'ipv6'  => filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6),
			'mac'   => filter_var($value, FILTER_VALIDATE_MAC),
			default => filter_var($value, $filter, $options)
		};
	}

	/**
	 * Parse string.
	 *
	 * @access public
	 * @param string $string
	 * @param array $result
	 * @return mixed
	 */
	public static function parse(string $string, &$result = []) : array
	{
		parse_str($string, $result);
		return $result;
	}

	/**
	 * Parse URL (URL toolkit).
	 *
	 * [SCHEME : 0].
	 * [HOST   : 1].
	 * [PATH   : 5].
	 * [QUERY  : 6].
	 *
	 * @access public
	 * @param string $url
	 * @param int $component
	 * @return mixed
	 */
	public static function parseUrl(string $url, int $component = -1) : mixed
	{
		return parse_url($url, $component);
	}

	/**
	 * Build query args from string (URL toolkit).
	 *
	 * [RFC1738: 1]
	 * [RFC3986: 2]
	 *
	 * @access public
	 * @param mixed $args
	 * @param string $prefix, Numeric index for args (array)
	 * @param string $sep, Args separator
	 * @param int $enc, Encoding type
	 * @return string
	 */
	public static function buildQuery(mixed $args, string $prefix = '', ?string $sep = '&', int $enc = 1) : string
	{
		return http_build_query($args, $prefix, $sep, $enc);
	}

	/**
	 * Generate MAC address.
	 *
	 * @access public
	 * @return string
	 */
	public static function generateMac() : string
	{
		$vals = [
			'0',
			'1',
			'2',
			'3',
			'4',
			'5',
			'6',
			'7',
			'8',
			'9',
			'A',
			'B',
			'C',
			'D',
			'E',
			'F'
		];
		$address = '';
		if ( count($vals) >= 1 ) {
			$address = ['00'];
			while (count($address) < 6) {
				shuffle($vals);
				$address[] = "{$vals[0]}{$vals[1]}";
			}
			$address = implode(':', $address);
		}
		return $address;
	}

	/**
	 * String map.
	 * 
	 * @access public
	 * @param mixed $value
	 * @param callable $callback
	 * @return mixed
	 */
	public static function deepMap($value, $callback) : mixed
	{
		if ( TypeCheck::isArray($value) ) {
			foreach ($value as $index => $item) {
				$value[$index] = self::deepMap($item, $callback);
			}

		} elseif ( TypeCheck::isObject($value) ) {
			$vars = get_object_vars($value);
			foreach ($vars as $name => $content) {
				$value->{$name} = self::deepMap($content, $callback);
			}

		} else {
			$value = call_user_func($callback, $value);
		}

		return $value;
	}

	/**
	 * Format dash (hyphen) into underscore.
	 *
	 * @access public
	 * @param mixed $value
	 * @param bool $isGlobal
	 * @param array $except
	 * @return mixed
	 */
	public static function undash(mixed $value, bool $isGlobal = false, array $except = []) : mixed
	{
		if ( TypeCheck::isString($value) ) {
			if ( !Arrayify::inArray($value, $except) ) {
				if ( $isGlobal ) {
					$value = self::uppercase($value);
				}
				return self::replace('-', '_', $value);
			}
			return $value;
		}

		if ( TypeCheck::isArray($value) ) {
			foreach ($value as $k => $v) {
				if ( !TypeCheck::isString($k) || Arrayify::inArray($k, $except) ) {
					continue;
				}
				if ( self::contains($k, '-') ) {
					unset($value[$k]);
					$k = self::undash($k);
				}
				// Recursively process the value
				$value[$k] = self::undash($v, $isGlobal, $except);
			}

		}

		return $value;
	}

	/**
	 * Get basename with path format.
	 *
	 * @access public
	 * @param string $path
	 * @param string $suffix
	 * @return string
	 */
	public static function basename(string $path, string $suffix = '') : string
	{
		$path = self::replace('\\', '/', $path);
		return basename($path, $suffix);
	}

	/**
	 * Get break to line.
	 *
	 * @access public
	 * @return string
	 */
	public static function break() : string
	{
		return PHP_EOL;
	}

	/**
	 * Convert string to interface.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function toInterface(string $string) : string
	{
		$i = self::lowercase($string);
		if ( !self::contains($i, 'interface') ) {
			$string = "{$string}Interface";
		}
		return $string;
	}

	/**
	 * Decode HTML.
	 *
	 * [QUOTES : 3].
	 *
	 * @access public
	 * @param string $string
	 * @param int $flags
	 * @param string $to, Encoding
	 * @return string
	 */
	public static function decodeHtml(string $string, int $flags = 3, ?string $to = 'UTF-8') : string
	{
		return html_entity_decode($string, $flags, $to);
	}

	/**
	 * Get PREG error message.
	 *
	 * @access private
	 * @param int $error
	 * @return string
	 */
	private static function getPregError(int $error) : string
	{
		return match ($error) {
			PREG_NO_ERROR              => 'No error',
			PREG_INTERNAL_ERROR        => 'Internal PCRE error',
			PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit exhausted',
			PREG_RECURSION_LIMIT_ERROR => 'Recursion limit exhausted',
			PREG_BAD_UTF8_ERROR        => 'Malformed UTF-8 data',
			PREG_BAD_UTF8_OFFSET_ERROR => 'Bad UTF-8 offset',
			PREG_JIT_STACKLIMIT_ERROR  => 'JIT stack limit exhausted',
			default                    => "Unknown PREG error (code: {$error})"
		};
	}

	/**
	 * Validate encoding name.
	 *
	 * @access private
	 * @param string $encoding
	 * @return bool
	 */
	private static function isValidEncoding(string $encoding) : bool
	{
		// Common encoding names validation
		$validEncodings = [
			'UTF-8',
			'ISO-8859-1',
			'ASCII',
			'UTF-16',
			'UTF-16LE',
			'UTF-16BE',
			'UTF-32',
			'UTF-32LE',
			'UTF-32BE',
			'Windows-1252',
			'ISO-8859-15',
			'ASCII//TRANSLIT//IGNORE',
			'UTF-8//TRANSLIT//IGNORE'
		];

		// Direct match or iconv availability check
		if ( in_array($encoding, $validEncodings, true) ) {
			return true;
		}

		// Use iconv to test if encoding is supported
		if ( function_exists('iconv') ) {
			$test = @iconv($encoding, 'UTF-8', 'test');
			return $test !== false;
		}

		return false;
	}

	/**
	 * Validate serialized format.
	 *
	 * @access private
	 * @param string $value
	 * @param bool $strict
	 * @return bool
	 */
	private static function validateSerializedFormat(string $value, bool $strict) : bool
	{
		// Check basic format
		if ( $value[1] !== ':' ) {
			return false;
		}

		if ( $strict ) {
			$lastc = substr($value, -1);
			if ( $lastc !== ';' && $lastc !== '}' ) {
				return false;
			}
		} else {
			$semicolon = strpos($value, ';');
			$brace = strpos($value, '}');
			if ( $semicolon === false && $brace === false ) {
				return false;
			}
			if ( $semicolon !== false && $semicolon < 3 ) {
				return false;
			}
			if ( $brace !== false && $brace < 4 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate serialized token.
	 *
	 * @access private
	 * @param string $token
	 * @param string $value
	 * @param bool $strict
	 * @return bool
	 */
	private static function validateSerializedToken(string $token, string $value, bool $strict) : bool
	{
		switch ($token) {
			case 's':
				return self::validateStringToken($value, $strict);
			case 'a':
			case 'O':
				return self::validateComplexToken($token, $value);
			case 'b':
			case 'i':
			case 'd':
				return self::validateScalarToken($token, $value, $strict);
			default:
				return false;
		}
	}

	/**
	 * Validate string token in serialized data.
	 *
	 * @access private
	 * @param string $value
	 * @param bool $strict
	 * @return bool
	 */
	private static function validateStringToken(string $value, bool $strict) : bool
	{
		if ( $strict ) {
			if ( substr($value, -2, 1) !== '"' ) {
				return false;
			}
		} elseif ( strpos($value, '"') === false ) {
			return false;
		}
		return true;
	}

	/**
	 * Validate complex token (array/object) in serialized data.
	 *
	 * @access private
	 * @param string $token
	 * @param string $value
	 * @return bool
	 */
	private static function validateComplexToken(string $token, string $value) : bool
	{
		try {
			self::match("/^{$token}:[0-9]+:/s", $value, $matches);
			return (bool)$matches;
		} catch (\RuntimeException $e) {
			return false;
		}
	}

	/**
	 * Validate scalar token in serialized data.
	 *
	 * @access private
	 * @param string $token
	 * @param string $value
	 * @param bool $strict
	 * @return bool
	 */
	private static function validateScalarToken(string $token, string $value, bool $strict) : bool
	{
		try {
			$end = $strict ? '$' : '';
			self::match("/^{$token}:[0-9.E+-]+;$end/", $value, $matches);
			return (bool)$matches;
		} catch (\RuntimeException $e) {
			return false;
		}
	}
}
