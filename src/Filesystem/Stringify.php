<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.3.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

/**
 * Advanced I/O helper and string manipulation.
 */
final class Stringify
{
	/**
	 * Search replace string(s).
	 * 
	 * @access public
	 * @param mixed $search
	 * @param mixed $replace
	 * @param mixed $subject
	 * @param int $count
	 * @return mixed
	 */
	public static function replace($search, $replace, $subject, ?int &$count = null) : mixed
	{
		return str_replace($search, $replace, $subject, $count);
	}

	/**
	 * Search replace substring(s).
	 *
	 * @access public
	 * @param mixed $search
	 * @param mixed $replace
	 * @param mixed $offset
	 * @param mixed $length
	 * @return mixed
	 */
	public static function subReplace($search, $replace, $offset = 0, $length = null) : mixed
	{
		return substr_replace($search, $replace, $offset, $length);
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
	 * @param mixed $regex
	 * @param mixed $replace
	 * @param mixed $subject
	 * @param int $limit
	 * @param int $count
	 * @return mixed
	 */
	public static function replaceRegex($regex, $replace, $subject, $limit = -1, &$count = null) : mixed
	{
		return preg_replace($regex, $replace, $subject, $limit, $count);
	}

	/**
	 * Search replace string(s) using regex callback.
	 *
	 * @access public
	 * @param mixed $regex
	 * @param mixed $callback
	 * @param mixed $subject
	 * @param int $limit
	 * @param int $count
	 * @return mixed
	 */
	public static function replaceRegexCb($regex, $callback, $subject, int $limit = -1, ?int &$count = null) : mixed
	{
		return preg_replace_callback($regex, $callback, $subject, $limit, $count);
	}

	/**
	 * Remove string from other string.
	 *
	 * @access public
	 * @param mixed $string
	 * @param mixed $subject
	 * @return mixed
	 */
	public static function remove($string, $subject) : mixed
	{
		return self::replace($string, '', $subject);
	}

	/**
	 * Remove sub string.
	 * 
	 * @access public
	 * @param string $string
	 * @param mixed $offset
	 * @param mixed $length
	 * @return string
	 */
	public static function subRemove(string $string, $offset = 0, $length = null) : string
	{
		if ( !$length ) {
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
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function slugify(string $string) : string
	{
		// Replace non letter or digits by -
		$slug = self::replaceRegex('~[^\pL\d]+~u', '-', (string)$string);

		// Transliterate
		$slug = strtr($slug, self::getSpecialChars());
		$slug = self::encode($slug, 'ASCII//TRANSLIT//IGNORE');

		// Remove unwanted characters
		$slug = self::replaceRegex('~[^-\w]+~', '', $slug);

		// Trim
		$slug = trim($slug, '-');

		// Remove duplicate -
		$slug = self::replaceRegex('~-+~', '-', $slug);

		// Lowercase
		return strtolower($slug);
	}

	/**
	 * Get special chars.
	 * 
	 * @access public
	 * @return array
	 */
	public static function getSpecialChars() : array
	{
		return (array)Json::parse(
			file: dirname(__FILE__) . '/bin/special.json',
			isArray: true
		);
	}

	/**
	 * Search string.
	 *
	 * @access public
	 * @param mixed $string
	 * @param string $search
	 * @return bool
	 */
	public static function contains($string, string $search) : bool
	{
		if ( TypeCheck::isArray($string) ) {
			return Arrayify::inArray($search, $string);
		}
		if ( strpos(haystack: (string)$string, needle: $search) !== false ) {
			return true;
		}
		return false;
	}

	/**
	 * Split string.
	 *
	 * @access public
	 * @param string $string
	 * @param array $args, [regex, limit, flags, length]
	 * @return mixed
	 */
	public static function split(string $string, array $args = []) : mixed
	{
		if ( isset($args['regex']) ) {
			$limit = $args['limit'] ?? -1;
			$flags = $args['flags'] ?? 0;
			return preg_split($args['regex'], $string, $limit, $flags);
		}
		$length = $args['length'] ?? 1;
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
	public static function chunk(string $string, int $length = 76, string $sep = "\r\n") : mixed
	{
		return chunk_split($string, $length, $sep);
	}

	/**
	 * Encode string | Default encode string to UTF-8.
	 *
	 * @access public
	 * @param string $string
	 * @param string $to
	 * @param string $from
	 * @return string
	 */
	public static function encode($string, $from = 'ISO-8859-1', $to = 'UTF-8') : string
	{
		if ( self::getEncoding($string, $to, true) !== self::uppercase($to) ) {
			if ( ($encoded = @iconv($to, $from, $string)) ) {
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
		if ( TypeCheck::isStream($path) ) {
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
		if ( !TypeCheck::isString($value) ) {
			return false;
		}
		$value = trim($value);
		if ( $value === 'N;' ) {
			return true;
		}
		if ( strlen($value) < 4 ) {
			return false;
		}
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
		$token = $value[0];
		switch ($token) {
			case 's':
				if ( $strict ) {
					if ( substr($value, -2, 1) !== '"' ) {
						return false;
					}
				} elseif ( strpos($value, '"') === false ) {
					return false;
				}
			case 'a':
			case 'O':
				self::match("/^{$token}:[0-9]+:/s", $value, $matches);
				return (bool)$matches;
			case 'b':
			case 'i':
			case 'd':
				$end = $strict ? '$' : '';
				self::match("/^{$token}:[0-9.E+-]+;$end/", $value, $matches);
				return (bool)$matches;
		}
		return false;
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
	 */
	public static function match(string $regex, string $string, &$matches, int $flags = 0, int $offset = 0) : bool
	{
		$shift = ($flags === -1) ? true : false;
		$flags = ($flags !== -1) ? $flags : 0;

		$matched = (bool)preg_match($regex, $string, $matches, $flags, $offset);

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
	 * @param int $length
	 * @param int $offset
	 * @param string $suffix
	 * @return string
	 */
	public static function limit(string $string, int $length = 128, int $offset = 0, ?string $suffix = '...') : string
	{
		$limit = $string;
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
			$limit = implode(Arrayify::slice($words, $offset, $last));
		}

		if ( empty($limit) ) {
			$limit = substr($string, $offset, $length);
		}

		if ( strlen($string) > $length ) {
			$limit .= " {$suffix}";
		}

		return trim($limit);
	}

	/**
	 * Filter string (Validation toolkit).
	 *
	 * [DEFAULT: 516].
	 *
	 * @access public
	 * @param mixed $value
	 * @param string $type
	 * @param int $filter
	 * @param mixed $options
	 * @return mixed
	 */
	public static function filter($value, ?string $type = 'name', int $filter = 516, $options = 0) : mixed
	{
		return match (self::lowercase((string)$type)) {
			'email' => filter_var($value, FILTER_SANITIZE_EMAIL),
			'name'  => filter_var($value, FILTER_DEFAULT, FILTER_FLAG_NO_ENCODE_QUOTES),
			'text'  => filter_var($value, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW),
			'url'   => filter_var($value, FILTER_SANITIZE_URL),
			default => filter_var($value, $filter, $options)
		};
	}

	/**
	 * Parse string (URL toolkit).
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
	 * [PHP_QUERY_RFC1738: 1]
	 * [PHP_QUERY_RFC3986: 2]
	 * 
	 * @access public
	 * @param mixed $args
	 * @param string $prefix, Numeric index for args (array)
	 * @param string $sep, Args separator
	 * @param int $enc, Encoding type
	 * @return string
	 */
	public static function buildQuery($args, string $prefix = '', ?string $sep = '&', int $enc = 1) : string
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
	 * @param string $string
	 * @param bool $isGlobal
	 * @return string
	 */
	public static function undash(string $string, bool $isGlobal = false) : string
	{
		if ( $isGlobal ) {
			$string = self::uppercase($string);
		}
		return self::replace('-', '_', $string);
	}

	/**
	 * Format dash (Alias).
	 *
	 * @access public
	 * @param mixed $value
	 * @param array $except
	 * @return mixed
	 */
	public static function underscore($value, array $except = []) : mixed
	{
		if ( TypeCheck::isArray($value) ) {
			foreach ($value as $k => $v) {
				if ( !TypeCheck::isString($k) || Arrayify::inArray($k, $except) ) {
					continue;
				}
				if ( self::contains($k, '-') ) {
					unset($value[$k]);
					$k = self::undash($k);
					$value[$k] = $v;
				}
			}
		}
		if ( TypeCheck::isString($value) && !Arrayify::inArray($value, $except) ) {
			$value = self::undash($value);
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
}
