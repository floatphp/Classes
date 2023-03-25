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

final class Stringify
{
	/**
	 * Search replace string(s).
	 * 
	 * @access public
	 * @param mixed $search
	 * @param mixed $replace
	 * @param string $subject
	 * @return string
	 */
	public static function replace($search, $replace, $subject)
	{
		return str_replace($search, $replace, (string)$subject);
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
	public static function subreplace($search, $replace, $offset = 0, $length = null)
	{
		return substr_replace($search, $replace, $offset, $length);
	}

	/**
	 * Search replace string(s) using array.
	 * 
	 * @access public
	 * @param array $search
	 * @param array $replace
	 * @return string
	 */
	public static function replaceArray($replace, $subject)
	{
		if ( TypeCheck::isArray($replace) ) {
			foreach ($replace as $key => $value) {
				$subject = self::replace($key, $value, $subject);
			}
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
	public static function replaceRegex($regex, $replace, $subject, $limit = -1, &$count = null)
	{
		return preg_replace($regex, $replace, $subject, $limit, $count);
	}

	/**
	 * Repeat string.
	 * 
	 * @access public
	 * @param string $string
	 * @param int $times
	 * @return string
	 */
	public static function repeat($string, $times = 0)
	{
		return str_repeat((string)$string, $times);
	}

	/**
	 * Lowercase string.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function lowercase($string)
	{
		return strtolower((string)$string);
	}

	/**
	 * Uppercase string.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function uppercase($string)
	{
		return strtoupper((string)$string);
	}

	/**
	 * Capitalize string.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function capitalize($string)
	{
		return ucfirst(self::lowercase($string));
	}

	/**
	 * Slugify string.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function slugify($string)
	{
	  	// Replace non letter or digits by -
	  	$slug = self::replaceRegex('~[^\pL\d]+~u','-', (string)$string);

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
	 * @param void
	 * @return array
	 */
	public static function getSpecialChars() : array
	{
		return (array)Json::parse(
			dirname(__FILE__) . '/bin/special.json',
			true
		);
	}

	/**
	 * Search string
	 *
	 * @access public
	 * @param mixed $string
	 * @param string $search
	 * @return bool
	 */
	public static function contains($string, $search)
	{
		if ( TypeCheck::isArray($string) ) {
			return Arrayify::inArray($search, $string);
		}
		if ( strpos((string)$string, $search) !== false ) {
			return true;
		}
		return false;
	}

	/**
	 * Split string.
	 *
	 * @access public
	 * @param string $string
	 * @param array $args, [regex,limit,flags,length]
	 * @return mixed
	 */
	public static function split($string, $args = [])
	{
		if ( isset($args['regex']) ) {
			$limit = $args['limit'] ?? -1;
			$flags = $args['flags'] ?? 0;
			return preg_split($args['regex'], (string)$string, $limit, $flags);
		}
		$length = $args['length'] ?? 1;
		return str_split((string)$string, $length);
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
	public static function encode($string, $from = 'ISO-8859-1', $to = 'UTF-8')
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
	public static function getEncoding($string, $encodings = null)
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
	public static function isUTF8($string)
	{
		$length = strlen($string);
		for ( $i = 0; $i < $length; $i++ ) {
			$c = ord($string[$i]);
			if ( $c < 0x80 ) {
				$n = 0; // 0bbbbbbb
			} elseif ( ( $c & 0xE0 ) == 0xC0 ) {
				$n = 1; // 110bbbbb
			} elseif ( ( $c & 0xF0 ) == 0xE0 ) {
				$n = 2; // 1110bbbb
			} elseif ( ( $c & 0xF8 ) == 0xF0 ) {
				$n = 3; // 11110bbb
			} elseif ( ( $c & 0xFC ) == 0xF8 ) {
				$n = 4; // 111110bb
			} elseif ( ( $c & 0xFE ) == 0xFC ) {
				$n = 5; // 1111110b
			} else {
				return false;
			}
			for ( $j = 0; $j < $n; $j++ ) {
				if ( (++$i == $length) || (( ord($string[$i]) & 0xC0) != 0x80) ) {
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
	public static function formatPath(string $path, $untrailing = false)
	{
	    $wrapper = '';

	    // Stream format
	    if ( TypeCheck::isStream($path) ) {
	        list($wrapper,$path) = explode('://', $path, 2);
	        $wrapper .= '://';
	    }

	    // Paths format
	    $path = self::replace('\\','/',$path);

	    // Multiple slashes format
	    $path = self::replaceRegex('|(?<=.)/+|', '/', $path);

	    // Windows format
	    if ( substr($path, 1, 1) === ':' ) {
	        $path = ucfirst($path);
	    }

	    // Untrailing slash
	    if ( $untrailing ) {
	    	return self::untrailingSlash("{$wrapper}{$path}");
	    }

	    return "{$wrapper}{$path}";
	}

	/**
	 * Format whitespaces,
	 * Including breaks.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function formatSpace($string)
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
	public static function formatKey($key)
	{
    	$key = self::lowercase($key);
    	return self::replaceRegex('/[^a-z0-9_\-]/', '', $key);
	}

	/**
	 * Remove slash from string.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function unSlash($string)
	{
	    return ltrim($string, '/\\');
	}

	/**
	 * Add slashes to string.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function slash($string)
	{
	    return '/' . self::unSlash($string);
	}

	/**
	 * Remove trailing slashes and backslashes if exist.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function untrailingSlash($string)
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
	public static function trailingSlash($string)
	{
	    return self::untrailingSlash($string) . '/';
	}

	/**
	 * Strip slashes from quotes,
	 * (array,object,scalar).
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function stripSlash($string)
	{
		return self::deepMap($string, function($string) {
			return TypeCheck::isString($string) 
			? stripslashes( $string ) : $string;
		});
	}

	/**
	 * Strip HTML tags from string.
	 * 
	 * @access public
	 * @param string $string
	 * @param bool $break
	 * @return string
	 */
	public static function stripTag($string, $break = false)
	{
		$string = (string)$string;
	    $string = self::replaceRegex('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
	    $string = strip_tags($string);
	    if ( $break ) {
	        $string = self::replaceRegex('/[\r\n\t ]+/', ' ', $string);
	    }
	    return trim($string);
	}

	/**
	 * Strip numbers from string,
	 * Using custom replace string.
	 * 
	 * @access public
	 * @param string $string
	 * @param string $replace
	 * @return string
	 */
	public static function stripNumber($string, $replace = '')
	{
		return self::replaceRegex('/[0-9]+/', $replace, (string)$string);
	}

	/**
	 * Strip characters from string,
	 * Using custom replace string.
	 * 
	 * @access public
	 * @param string $string
	 * @param string $replace
	 * @return string
	 */
	public static function stripChar($string, $replace = '')
	{
		return self::replaceRegex('/[^a-zA-Z0-9\s]/', $replace, (string)$string);
	}

	/**
	 * Strip spaces from string,
	 * Using custom replace string.
	 * 
	 * @access public
	 * @param string $string
	 * @param string $replace
	 * @return string
	 */
	public static function stripSpace($string, $replace = '')
	{
		return self::replaceRegex('/\s+/', $replace, trim((string)$string));
	}

	/**
	 * Strip break from string,
	 * Using custom replace string.
	 * 
	 * @access public
	 * @param string $string
	 * @param string $replace
	 * @return string
	 */
	public static function stripBreak($string, $replace = '')
	{
		return self::replaceRegex('/\r|\n/', $replace, (string)$string);
	}

	/**
	 * Unserialize value only if it was serialized.
	 *
	 * @access public
	 * @param string $string
	 * @return mixed
	 */
	public static function unserialize($string)
	{
		if ( self::isSerialized($string) ) {
			return @unserialize(trim($string));
		}
		return $string;
	}

	/**
	 * @access public
	 * @param mixed $value
	 * @return mixed
	 */
	public static function serialize($value)
	{
	    if ( TypeCheck::isArray($value) || TypeCheck::isObject($value) ) {
	        return serialize($value);
	    }
	    if ( self::isSerialized($value, false) ) {
	        return serialize($value);
	    }
	    return $value;
	}

	/**
	 * @access public
	 * @param string $value
	 * @param bool $strict
	 * @return bool
	 */
	public static function isSerialized($value, $strict = true)
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
	        if ( $semicolon === false  && $brace === false ) {
	            return false;
	        }
	        if ( $semicolon !== false && $semicolon < 3 ) {
	            return false;
	        }
	        if ( $brace !== false  && $brace < 4 ) {
	            return false;
	        }
	    }
	    $token = $value[0];
	    switch ( $token ) {
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
	            return (bool)self::match("/^{$token}:[0-9]+:/s", $value);
	        case 'b':
	        case 'i':
	        case 'd':
	            $end = $strict ? '$' : '';
	            return (bool)self::match("/^{$token}:[0-9.E+-]+;$end/", $value);
	    }
	    return false;
	}

	/**
	 * @access public
	 * @param string $regex
	 * @param string $string
	 * @param int $index
	 * @param int $flags
	 * @param int $offset
	 * @return mixed
	 */
	public static function match($regex, $string, $index = 0, $flags = 0, $offset = 0)
	{
		preg_match($regex,(string)$string, $matches, $flags, $offset);
		if ( $index === -1 ) {
			return $matches;
		}
		return $matches[$index] ?? false;
	}

	/**
	 * @access public
	 * @param string $regex
	 * @param string $string
	 * @param int $index
	 * @param int $flags
	 * @param int $offset
	 * @return mixed
	 */
	public static function matchAll($regex, $string, $index = 0, $flags = 0, $offset = 0)
	{
		preg_match_all($regex,(string)$string, $matches, $flags, $offset);
		if ( $index === -1 ) {
			return $matches;
		}
		return $matches[$index] ?? false;
	}

	/**
	 * Get random string.
	 * 
	 * @access public
	 * @param int|null $length
	 * @param string $char
	 * @return string
	 */
	public static function randomize($length, $char = '') : string
	{
		if ( !$char ) {
			$char  = implode(range('a', 'f'));
			$char .= implode(range('0', '9'));
		}
		$shuffled = self::shuffle($char);
		return substr($shuffled, 0, $length);
	}

	/**
	 * Shuffle string.
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function shuffle($string) : string
	{
		return str_shuffle($string);
	}

	/**
	 * @access public
	 * @param string $string
	 * @param int $length
	 * @param bool $more
	 * @return string
	 */
	public static function limit(string $string, $length = 100, $more = true) : string
	{
		if ( strlen($string) > $length ) {
			$string = substr($string, 0, 80);
			if ( $more ) {
				$string = "{$string} [...]";
			}
		}
		return $string;
	}

	/**
	 * Filter string.
	 * 
	 * FILTER_DEFAULT: 516
	 * 
	 * @access public
	 * @param mixed $value
	 * @param mixed $type
	 * @param int $filter
	 * @param array|int $options
	 * @return mixed
	 */
	public static function filter($value, $type = '', $filter = 516, $options = 0)
	{
		switch (self::lowercase($type)) {
			case 'email':
				return filter_var($value, FILTER_SANITIZE_EMAIL);
				break;

			case 'name':
				return filter_var($value, FILTER_DEFAULT, FILTER_FLAG_NO_ENCODE_QUOTES);
				break;

			case 'subject':
				return filter_var($value, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
				break;

			case 'url':
			case 'link':
				return filter_var($value, FILTER_SANITIZE_URL);
				break;
		}
		return filter_var($value, $filter, $options);
	}

	/**
	 * Parse string (URL toolkit).
	 * 
	 * @access public
	 * @param string $string
	 * @param array $result
	 * @return mixed
	 */
	public static function parse($string, &$result = null)
	{
		parse_str($string, $result);
		return $result;
	}

	/**
	 * Parse URL (URL toolkit).
	 * 
	 * PHP_URL_SCHEME : 0
	 * PHP_URL_HOST : 1
	 * PHP_URL_PATH : 5
	 * PHP_URL_QUERY : 6ss
	 * 
	 * @access public
	 * @param string $url
	 * @param int $component
	 * @return mixed
	 * 
	 */
	public static function parseUrl($url, $component = -1)
	{
		return parse_url((string)$url, $component);
	}

	/**
	 * Build query args from string (URL toolkit).
	 * 
	 * PHP_QUERY_RFC1738 : 1
	 * 
	 * @access public
	 * @param mixed $args
	 * @param string $prefix, Numeric index for args (array)
	 * @param string $sep, Args separator
	 * @param string $enc, Encoding type
	 * @return string
	 */
	public static function buildQuery($args, $prefix = '', $sep = '', $enc = 1)
	{
		return http_build_query($args, $prefix, $sep, $enc);
	}

    /**
     * Generate MAC address.
     *
     * @access public
     * @param void
     * @return string
     */
    public static function generateMAC() : string
    {
        $vals = [
            '0', '1', '2', '3', '4', '5', '6', '7',
            '8', '9', 'A', 'B', 'C', 'D', 'E', 'F'
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
	 * @access private
	 * @param mixed $value
	 * @param callable $callback
	 * @return mixed
	 */
	private static function deepMap($value, $callback)
	{
	    if ( TypeCheck::isArray($value) ) {
	        foreach ( $value as $index => $item ) {
	            $value[$index] = self::deepMap($item, $callback);
	        }
	    } elseif ( TypeCheck::isObject($value) ) {
	        $vars = get_object_vars($value);
	        foreach ($vars as $name => $content) {
	            $value->$name = self::deepMap($content, $callback);
	        }
	    } else {
	        $value = call_user_func($callback, $value);
	    }
	    return $value;
	}
}
