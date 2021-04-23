<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Html Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Html;

class Shortcode extends Hook
{
	/**
	 * @access public
	 * @var array $shortcodeTags
	 */
	public static $shortcodeTags = [];

	/**
	 * Prevent object construction
	 *
	 * @param void
	 */
	public function __construct()
	{
		die(__METHOD__.': Construct denied');
	}

	/**
	 * Prevent object clone
	 *
	 * @param void
	 */
    public function __clone()
    {
        die(__METHOD__.': Clone denied');
    }

	/**
	 * Prevent object serialization
	 *
	 * @param void
	 */
    public function __wakeup()
    {
        die(__METHOD__.': Unserialize denied');
    }

	/**
	 * Add hook for shortcode tag
	 *
	 * @access public
	 * @param string $tag
	 * @param string $callable
	 * @return bool
	 */
	public function addShortcode($tag, $callable)
	{
		if ( is_callable($callable) ) {
			self::$shortcodeTags[$tag] = $callable;
			return true;
		}
		return false;
	}

	/**
	 * Removes hook for shortcode
	 *
	 * @access public
	 * @param string $tag
	 * @return bool
	 */
	public function removeShortcode($tag)
	{
		if ( isset(self::$shortcodeTags[$tag]) ) {
			unset(self::$shortcodeTags[$tag]);
			return true;
		}
		return false;
	}

	/**
	 * Clears all of the shortcode tags
	 *
	 * @access public
	 * @param void
	 * @return true
	 */
	public function removeAllShortcodes()
	{
		self::$shortcodeTags = [];
		return true;
	}

	/**
	 * Whether a registered shortcode exists named $tag
	 *
	 * @access public
	 * @param string $tag
	 * @param string $callableToCheck false
	 * @return mixed
	 */
	public function shortcodeExists($tag)
	{
		return array_key_exists($tag,self::$shortcodeTags);
	}

	/**
	 * Whether the passed content contains the specified shortcode
	 *
	 * @access public
	 * @param string $content
	 * @param string $tag
	 * @return bool
	 */
	public function hasShortcode($content, $tag)
	{
		if ( false === strpos($content, '[') ) {
			return false;
		}
		if ( $this->shortcodeExists($tag) ) {
			preg_match_all("/{$this->getShortcodeRegex()}/s",$content,$matches,PREG_SET_ORDER);
			if ( empty($matches) ) {
				return false;
			}
			foreach ($matches as $shortcode) {
				if ( $tag === $shortcode[2] ) {
					return true;
				}
				if ( !empty($shortcode[5]) && $this->hasShortcode($shortcode[5], $tag) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Search content for shortcodes and filter shortcodes through their hooks
	 *
	 * @access public
	 * @param string $tag
	 * @param string $callableToCheck false
	 * @return mixed
	 */
	public function doShortcode($content)
	{
		if ( empty(self::$shortcodeTags) || !is_array(self::$shortcodeTags) ) {
			return $content;
		}
		$pattern = $this->getShortcodeRegex();
		return preg_replace_callback("/{$pattern}/s",[$this,'doShortcodeTag'],$content);
	}

	/**
	 * Retrieve the shortcode regular expression for searching
	 *
	 * @access public
	 * @param string void
	 * @return string
	 */
	public function getShortcodeRegex()
	{
		$tagnames = array_keys(self::$shortcodeTags);
		$tagregexp = implode('|',array_map('preg_quote',$tagnames));
		return
		  '\\['
		  . '(\\[?)'
		  . "($tagregexp)"
		  . '(?![\\w-])'
		  . '('
		  . '[^\\]\\/]*'
		  . '(?:'
		  . '\\/(?!\\])'
		  . '[^\\]\\/]*'
		  . ')*?'
		  . ')'
		  . '(?:'
		  . '(\\/)'
		  . '\\]'
		  . '|'
		  . '\\]'
		  . '(?:'
		  . '('
		  . '[^\\[]*+'
		  . '(?:'
		  . '\\[(?!\\/\\2\\])'
		  . '[^\\[]*+'
		  . ')*+'
		  . ')'
		  . '\\[\\/\\2\\]'
		  . ')?'
		  . ')'
		  . '(\\]?)';
	}

	/**
	 * Regular Expression callable for doShortcode() for calling shortcode hook
	 *
	 * @access public
	 * @param string $tag
	 * @param string $callableToCheck false
	 * @return mixed
	 */
	private function doShortcodeTag($m)
	{
		// allow [[foo]] syntax for escaping a tag
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return substr($m[0], 1, -1);
		}
		$tag = $m[2];
		$attr = $this->shortcodeParseAtts($m[3]);
		// enclosing tag - extra parameter
		if ( isset($m[5]) ) {
			return $m[1] . call_user_func(self::$shortcodeTags[$tag],$attr,$m[5],$tag) . $m[6];
		}
		// self-closing tag
		return $m[1] . call_user_func(self::$shortcodeTags[$tag],$attr,null,$tag) . $m[6];
	}

	/**
	 * Retrieve all attributes from the shortcodes tag
	 *
	 * @access public
	 * @param string $tag
	 * @param string $callableToCheck false
	 * @return mixed
	 */
	public function shortcodeParseAtts($text)
	{
		$atts = [];
		$pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
		$text = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $text);
		if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
			foreach ($match as $m) {
				if ( !empty($m[1]) ) {
					$atts[strtolower($m[1])] = stripcslashes($m[2]);

				} elseif ( !empty($m[3]) ) {
					$atts[strtolower($m[3])] = stripcslashes($m[4]);

				} elseif ( !empty($m[5]) ) {
					$atts[strtolower($m[5])] = stripcslashes($m[6]);

				} elseif ( isset($m[7]) && $m[7] !== '' ) {
					$atts[] = stripcslashes($m[7]);

				} elseif ( isset($m[8]) ) {
					$atts[] = stripcslashes($m[8]);
				}
			}
		} else {
			$atts = ltrim($text);
		}
		return $atts;
	}

	/**
	 * Combine user attributes with known attributes and fill in defaults when needed
	 *
	 * @access public
	 * @param string $pairs
	 * @param string $atts
	 * @param string $shortcode
	 * @return mixed
	 */
	public function shortcodeAtts($pairs, $atts, $shortcode = '')
	{
		$atts = (array)$atts;
		$out = [];
		foreach ($pairs as $name => $default) {
			if ( array_key_exists($name, $atts) ) {
				$out[$name] = $atts[$name];
			} else {
				$out[$name] = $default;
			}
		}
		if ( $shortcode ) {
			$out = $this->applyFilter([
			    $this,
			    "shortcodeAtts_{$shortcode}"
			  ], $out, $pairs, $atts
			);
		}
		return $out;
	}

	/**
	 * Remove all shortcode tags from the given content
	 *
	 * @access public
	 * @param string $tag
	 * @param string $callableToCheck false
	 * @return mixed
	 */
	public function stripShortcodes($content)
	{
		if ( empty(self::$shortcodeTags) || !is_array(self::$shortcodeTags) ) {
			return $content;
		}
		$pattern = $this->getShortcodeRegex();
		return preg_replace_callback("/$pattern/s",[$this,'stripShortcodeTag'],$content);
	}

	/**
	 * Strip shortcode by tag
	 *
	 * @access private
	 * @param string $tag
	 * @param string $callableToCheck false
	 * @return mixed
	 */
	private function stripShortcodeTag($m)
	{
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return substr($m[0], 1, -1);
		}
		return $m[1] . $m[6];
	}
}
