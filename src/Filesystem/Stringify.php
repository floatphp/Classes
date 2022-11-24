<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.0.0
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2022 Jihad Sinnaour <mail@jihadsinnaour.com>
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
	 * @access public
	 * @param array $search
	 * @param array $replace
	 * @param string $subject
	 * @return string
	 */
	public static function replace($search, $replace, $subject)
	{
		return str_replace($search,$replace,$subject);
	}

	/**
	 * @access public
	 * @param mixed $search
	 * @param mixed $replace
	 * @param mixed $offset
	 * @param mixed $length
	 * @return mixed
	 */
	public static function subreplace($search, $replace, $offset, $length = null)
	{
		return substr_replace($search,$replace,$offset,$length);
	}

	/**
	 * @access public
	 * @param array $search
	 * @param array $replace
	 * @return string
	 */
	public static function replaceArray($replace, $subject)
	{
		if ( TypeCheck::isArray($replace) ) {
			foreach ($replace as $key => $value) {
				$subject = self::replace($key,$value,$subject);
			}
		}
		return $subject;
	}

	/**
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
		return preg_replace($regex,$replace,$subject,$limit,$count);
	}

	/**
	 * @access public
	 * @param string $regex
	 * @param int $times
	 * @return string
	 */
	public static function repeat($string, $times)
	{
		return str_repeat($string,$times);
	}

	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function lowercase($string) : string
	{
		return strtolower((string)$string);
	}

	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function uppercase($string)
	{
		return strtoupper($string);
	}

	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function capitalize($string)
	{
		return ucfirst(self::lowercase($string));
	}

	/**
	 * @access public
	 * @param array $array
	 * @return object
	 */
	public static function toObject($array = [])
	{
	    if ( empty($array) || !TypeCheck::isArray($array) ) {
	    	return false;
	    }
	    $obj = new \stdClass;
	    foreach ( $array as $key => $val ) {
	        $obj->{$key} = $val;
	    }
	    return (object)$obj;
	}

	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function slugify($string)
	{
	  	// Replace non letter or digits by -
	  	$slug = self::replaceRegex('~[^\pL\d]+~u','-',(string)$string);
	  	// Transliterate
	  	$slug = strtr($slug,self::getSpecialChars());
	  	$slug = self::encode($slug,'ASCII//TRANSLIT//IGNORE');
	  	// Remove unwanted characters
	  	$slug = self::replaceRegex('~[^-\w]+~','',$slug);
	  	// Trim
	  	$slug = trim($slug,'-');
	  	// Remove duplicate -
	  	$slug = self::replaceRegex('~-+~','-',$slug);
	  	// Lowercase
	  	return strtolower($slug);
	}

	/**
	 * @access public
	 * @param void
	 * @return array
	 */
	public static function getSpecialChars() : array
	{
		return (array)Json::parse(dirname(__FILE__).'/bin/special.json',true);
	}

	/**
	 * Search string
	 *
	 * @access public
	 * @param string|array $string
	 * @param string $search
	 * @return bool
	 */
	public static function contains($string, $search)
	{
		if ( TypeCheck::isArray($string) ) {
			return Arrayify::inArray($search,$string);
		}
		if ( strpos($string,$search) !== false ) {
			return true;
		}
		return false;
	}

	/**
	 * Split string
	 *
	 * @access public
	 * @param string $string
	 * @param array $args
	 * @return mixed
	 */
	public static function split($string, $args = [])
	{
		if ( isset($args['regex']) ) {
			$limit = isset($args['$limit']) ? $args['$limit'] : -1;
			$flags = isset($args['$flags']) ? $args['$flags'] : 0;
			return preg_split($args['regex'],$string,$limit,$flags);
		} else {
			$length = isset($args['length']) ? $args['length'] : 1;
			return str_split($string,$length);
		}
	}

	/**
	 * Format Path
	 *
	 * @access public
	 * @param string $path
	 * @param bool $untrailing
	 * @return string
	 */
	public static function formatPath($path, $untrailing = false)
	{
	    $wrapper = '';
	    // Stream format
	    if ( TypeCheck::isStream($path) ) {
	        list($wrapper,$path) = explode('://',$path,2);
	        $wrapper .= '://';
	    }
	    // Paths format
	    $path = self::replace('\\','/',$path);
	    // Multiple slashes format
	    $path = self::replaceRegex('|(?<=.)/+|','/',$path);
	    // Windows format
	    if ( substr($path,1,1) === ':' ) {
	        $path = ucfirst($path);
	    }
	    // Untrailing Slash
	    if ( $untrailing ) {
	    	return self::untrailingSlash("{$wrapper}{$path}");
	    }
	    return "{$wrapper}{$path}";
	}

	/**
	 * @access public
	 * @param string $key
	 * @return string
	 */
	public static function formatKey($key)
	{
    	$key = self::lowercase($key);
    	return self::replaceRegex('/[^a-z0-9_\-]/','',$key);
	}

	/**
	 * Encode string
	 *
	 * @access public
	 * @param string $string
	 * @param string $to
	 * @param string $from
	 * @return string
	 */
	public static function encode($string, $from = 'ISO-8859-1', $to = 'UTF-8')
	{
		$from = self::uppercase($from);
		$to = self::uppercase($to);
		if ( self::getEncoding($string,$to) !== $to ) {
			if ( $from  == 'ISO-8859-1' && $to == 'UTF-8' ) {
				$string = self::encodeUTF8($string);
			} else {
				$string = @iconv($to,$from,$string);
			}
		}
		return $string;
	}

	/**
	 * Decode string
	 *
	 * @access public
	 * @param string $string
	 * @param string $to
	 * @param string $from
	 * @return string
	 */
	public static function decode($string, $from = 'UTF-8', $to = 'ISO-8859-1')
	{
		$from = self::uppercase($from);
		$to = self::uppercase($to);
		if ( self::getEncoding($string,$to) !== $to ) {
			if ( $from == 'UTF-8' && $to == 'ISO-8859-1' ) {
				$string = self::decodeUTF8($string);
			} else {
				$string = @iconv($from,$to,$string);
			}
		}
		return $string;
	}

	/**
	 * Decode string UTF-8
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function decodeUTF8($string)
	{
		return utf8_decode($string);
	}

	/**
	 * Encode string UTF-8
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function encodeUTF8($string)
	{
		return utf8_encode($string);
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
		return mb_detect_encoding($string,$encodings);
	}

	/**
	 * Parse string
	 *
	 * @access public
	 * @param string $string
	 * @param array $result
	 * @return mixed
	 */
	public static function parse($string, &$result = null)
	{
		parse_str($string,$result);
		return $result;
	}

	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function unSlash($string)
	{
	    return ltrim($string,'/\\');
	}
	
	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function slash($string)
	{
	    return '/' . self::unSlash($string);
	}

	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function untrailingSlash($string)
	{
	    return rtrim($string,'/\\');
	}

	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function trailingSlash($string)
	{
	    return self::untrailingSlash($string) . '/';
	}

	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function slashStrip($string)
	{
		return self::deepMap($string, function($string) {
			return TypeCheck::isString( $string ) ? stripslashes( $string ) : $string;
		});
	}

	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function numberStrip($string)
	{
		return self::replaceRegex('/[0-9]+/','',$string);
	}

	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function charStrip($string)
	{
		return self::replaceRegex('/[^a-zA-Z0-9\s]/','',$string);
	}

	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function spaceStrip($string)
	{
		return self::replaceRegex('/\s+/','',trim($string));
	}

	/**
	 * @access public
	 * @param string $string
	 * @param bool $break
	 * @return string
	 */
	public static function tagStrip($string, $break = false)
	{
		$string = (string)$string;
	    $string = self::replaceRegex('@<(script|style)[^>]*?>.*?</\\1>@si','',$string);
	    $string = strip_tags($string);
	    if ( $break ) {
	        $string = self::replaceRegex('/[\r\n\t ]+/',' ',$string);
	    }
	    return trim($string);
	}

	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function breakStrip($string)
	{
		return self::replaceRegex('/\r|\n/', '', $string);
	}

	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function normalizeSpace($string)
	{
	    return self::replaceRegex('/\s+/u',' ',trim($string));
	}

	/**
	 * @access public
	 * @param string $data
	 * @return mixed
	 */
	public static function unserialize($data)
	{
		if ( self::isSerialized($data) ) {
			return @unserialize(trim($data));
		}
		return $data;
	}

	/**
	 * @access public
	 * @param string $data
	 * @return string
	 */
	public static function serialize($data)
	{
	    if ( TypeCheck::isArray($data) || TypeCheck::isObject($data) ) {
	        return serialize($data);
	    }
	    if ( self::isSerialized($data, false) ) {
	        return serialize($data);
	    }
	    return $data;
	}

	/**
	 * @access public
	 * @param array $array
	 * @param bool $strict
	 * @return bool
	 */
	public static function isSerialized($data, $strict = true)
	{
	    if ( !TypeCheck::isString($data) ) {
	        return false;
	    }
	    $data = trim($data);
	    if ( 'N;' === $data ) {
	        return true;
	    }
	    if ( strlen($data) < 4 ) {
	        return false;
	    }
	    if ( $data[1] !== ':' ) {
	        return false;
	    }
	    if ( $strict ) {
	        $lastc = substr($data,-1);
	        if ( $lastc !== ';' && $lastc !== '}' ) {
	            return false;
	        }
	    } else {
	        $semicolon = strpos($data,';');
	        $brace = strpos($data,'}');
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
	    $token = $data[0];
	    switch ( $token ) {
	        case 's':
	            if ( $strict ) {
	                if ( substr($data,-2,1) !== '"' ) {
	                    return false;
	                }
	            } elseif ( strpos($data,'"') === false ) {
	                return false;
	            }
	        case 'a':
	        case 'O':
	            return (bool)self::match("/^{$token}:[0-9]+:/s",$data);
	        case 'b':
	        case 'i':
	        case 'd':
	            $end = $strict ? '$' : '';
	            return (bool)self::match("/^{$token}:[0-9.E+-]+;$end/",$data);
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
		preg_match($regex,(string)$string,$matches,$flags,$offset);
		if ( $index === -1 ) {
			return $matches;
		}
		return isset($matches[$index]) ? $matches[$index] : false;
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
		preg_match_all($regex,(string)$string,$matches,$flags,$offset);
		if ( $index === -1 ) {
			return $matches;
		}
		return isset($matches[$index]) ? $matches[$index] : false;
	}

	/**
	 * @access public
	 * @param string $length
	 * @param string $char
	 * @return string
	 */
	public static function randomize($length, $char = '') : string
	{
		if ( !$char ) {
			$char  = implode(range('a','f'));
			$char .= implode(range('0','9'));
		}
		$shuffled = self::shuffle($char);
		return substr($shuffled,0,$length);
	}

	/**
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function shuffle($string) : string
	{
		return str_shuffle($string);
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
