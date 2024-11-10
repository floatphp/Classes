<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Html Component
 * @version    : 1.2.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Html;

use FloatPHP\Classes\Filesystem\{
	Arrayify, Stringify, TypeCheck
};

/**
 * Built-in Shortcode class,
 * @uses Inspired by WordPress kernel https://make.wordpress.org
 */
final class Shortcode extends Hook
{
	/**
	 * @access public
	 * @var array SPIN, Spintax pattern
	 */
	public const SPIN = '/\{(((?>[^\{\}]+)|(?R))*?)\}/x';

	/**
	 * @access public
	 * @var array $shortcodeTags
	 */
	public $shortcodeTags = [];

	/**
	 * Get singleton hook instance.
	 *
	 * @access public
	 * @return object
	 */
	public static function getInstance() : Shortcode
	{
		static $instance;
		if ( $instance === null ) {
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * Add shortcode.
	 *
	 * @access public
	 * @param string $tag
	 * @param callable $callback
	 * @return bool
	 */
	public function addShortcode(string $tag, $callback) : bool
	{
		if ( TypeCheck::isCallable($callback) ) {
			$this->shortcodeTags[$tag] = $callback;
			return true;
		}
		return false;
	}

	/**
	 * Remove shortcode.
	 *
	 * @access public
	 * @param string $tag
	 * @return bool
	 */
	public function removeShortcode(string $tag) : bool
	{
		if ( isset($this->shortcodeTags[$tag]) ) {
			unset($this->shortcodeTags[$tag]);
			return true;
		}
		return false;
	}

	/**
	 * Remove all shortcodes.
	 *
	 * @access public
	 * @return void
	 */
	public function removeShortcodes()
	{
		$this->shortcodeTags = [];
	}

	/**
	 * Check whether shortcode exists.
	 *
	 * @access public
	 * @param string $tag
	 * @return bool
	 */
	public function shortcodeExists(string $tag) : bool
	{
		return Arrayify::hasKey($tag, $this->shortcodeTags);
	}

	/**
	 * Check whether content contains shortcode.
	 *
	 * @access public
	 * @param string $content
	 * @param string $tag
	 * @return bool
	 */
	public function hasShortcode(string $content, string $tag) : bool
	{
		if ( strpos($content, '[') === false ) {
			return false;
		}

		if ( $this->shortcodeExists($tag) ) {

			$regex = "/{$this->getShortcodeRegex()}/s";
			preg_match_all($regex, $content, $matches, 2);
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
	 * Do shortcode hook.
	 *
	 * @access public
	 * @param string $tag
	 * @return mixed
	 */
	public function doShortcode($tag)
	{
		if ( empty($this->shortcodeTags) || !TypeCheck::isArray($this->shortcodeTags) ) {
			return $tag;
		}
		$pattern = $this->getShortcodeRegex();
		return preg_replace_callback("/{$pattern}/s", [$this, 'doShortcodeTag'], $tag);
	}

	/**
	 * Get shortcode regex.
	 *
	 * @access public
	 * @param string void
	 * @return string
	 */
	public function getShortcodeRegex()
	{
		$tagnames  = array_keys($this->shortcodeTags);
		$tagregexp = implode('|', array_map('preg_quote', $tagnames));

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
	 * Parse shortcodes attributes.
	 *
	 * @access public
	 * @param mixed $content
	 * @return mixed
	 */
	public function parseAtts($content)
	{
		$atts = [];
		$pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
		$content = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $content);

		if ( preg_match_all($pattern, $content, $match, PREG_SET_ORDER) ) {
			foreach ($match as $tag) {
				if ( !empty($tag[1]) ) {
					$atts[strtolower($tag[1])] = stripcslashes($tag[2]);

				} elseif ( !empty($tag[3]) ) {
					$atts[strtolower($tag[3])] = stripcslashes($tag[4]);

				} elseif ( !empty($tag[5]) ) {
					$atts[strtolower($tag[5])] = stripcslashes($tag[6]);

				} elseif ( isset($tag[7]) && $tag[7] !== '' ) {
					$atts[] = stripcslashes($tag[7]);

				} elseif ( isset($tag[8]) ) {
					$atts[] = stripcslashes($tag[8]);
				}
			}

		} else {
			$atts = ltrim($content);
		}

		// Format atts
		if ( is_array($atts) && count($atts) == 1 ) {
			$key = array_key_first($atts);
			if ( is_int($key) ) {
				$atts = $atts[$key];
			}
		}

		return $atts;
	}

	/**
	 * Set default shortocde attributes.
	 *
	 * @access public
	 * @param array $pairs
	 * @param mixed $atts
	 * @param string $shortcode
	 * @return mixed
	 */
	public function shortcodeAtts($pairs, $atts, $shortcode = '')
	{
		$atts = (array)$atts;
		$out = [];
		foreach ($pairs as $name => $default) {
			if ( Arrayify::hasKey($name, $atts) ) {
				$out[$name] = $atts[$name];

			} else {
				$out[$name] = $default;
			}
		}
		if ( $shortcode ) {
			$out = $this->applyFilter([$this, "shortcodeAtts-{$shortcode}"], $out, $pairs, $atts);
		}
		return $out;
	}

	/**
	 * Remove all shortcodes from content.
	 *
	 * @access public
	 * @param string $content
	 * @return string
	 */
	public function stripShortcodes($content)
	{
		if ( empty($this->shortcodeTags) 
		  || !TypeCheck::isArray($this->shortcodeTags) ) {
			return $content;
		}
		
		$pattern = $this->getShortcodeRegex();
		return preg_replace_callback("/$pattern/s", [$this, 'stripShortcodeTag'], $content);
	}

	/**
	 * Spin string.
	 *
	 * @access public
	 * @param string $content
	 * @return string
	 */
    public static function spin(string $content) : string
    {
        $cb = function ($match) {
            $content = self::spin($match[1]);
            $parts = explode('|', $content);
            $rand = mt_rand(0, count($parts) - 1);
            return $parts[$rand];
        };

        $value = Stringify::replaceRegexCb(self::SPIN, $cb, $content);
        return $value ?: $content;
    }

	/**
	 * Do shortcode hook tag.
	 *
	 * @access public
	 * @param array $tag
	 * @return mixed
	 * @todo
	 */
	private function doShortcodeTag(array $tag)
	{
		if ( count($tag) < 7 ) return; 

		$first = $tag[1] ?? '';
		$last  = $tag[6] ?? '';

		// Allow [[foo]] syntax for escaping a tag
		if ( $first == '[' && $last == ']' ) {
			return substr($tag[0], 1, -1);
		}

		$name = $tag[2] ?? '';
		$atts = $tag[3] ?? '';
		$atts = $this->parseAtts($atts);

		// Set callback
		$cb = $this->shortcodeTags[$name] ?? function(){ return 'error'; };

		// enclosing tag - extra parameter
		$open = $tag[5] ?? false;
		if ( $open ) {
			return $first . call_user_func($cb, $atts, $open, $tag) . $last;
		}

		// self-closing tag
		$close = $tag[5] ?? false;
		if ( $close ) {
			return $first . call_user_func($cb, $atts, null, $tag) . $last;
		}

		return call_user_func($cb, $atts, null, $name);
	}

	/**
	 * Strip shortcode by tag.
	 *
	 * @access private
	 * @param string $tag
	 * @return string
	 */
	private function stripShortcodeTag($tag)
	{
		if ( $tag[1] == '[' && $tag[6] == ']' ) {
			return substr($tag[0], 1, -1);
		}
		return "{$tag[1]}{$tag[6]}";
	}
}
