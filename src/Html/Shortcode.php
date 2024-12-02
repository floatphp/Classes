<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Html Component
 * @version    : 1.3.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Html;

use FloatPHP\Classes\Filesystem\{Stringify, Arrayify, TypeCheck};
use FloatPHP\Classes\Security\Sanitizer;

/**
 * Built-in shortcode class.
 * @see https://developer.wordpress.org/apis/shortcode/
 */
final class Shortcode extends Hook
{
	/**
	 * @access public
	 * @var array SPIN, Spintax regex
	 */
	public const SPIN = '/\{(((?>[^\{\}]+)|(?R))*?)\}/x';

	/**
	 * @access private
	 * @var array $tags
	 */
	private $tags = [];

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
	 * @param string $name
	 * @param callable $callback
	 * @return bool
	 */
	public function add(string $name, $callback) : bool
	{
		if ( TypeCheck::isCallable($callback) ) {
			$this->tags[$name] = $callback;
			return true;
		}
		return false;
	}

	/**
	 * Remove registered shortcode.
	 *
	 * @access public
	 * @param string $name
	 * @return bool
	 */
	public function remove(string $name) : bool
	{
		if ( isset($this->tags[$name]) ) {
			unset($this->tags[$name]);
			return true;
		}
		return false;
	}

	/**
	 * Remove all registered shortcodes.
	 *
	 * @access public
	 * @return bool
	 */
	public function removeAll() : bool
	{
		$this->tags = [];
		return true;
	}

	/**
	 * Check whether shortcode is registered.
	 *
	 * @access public
	 * @param string $name
	 * @return bool
	 */
	public function has(string $name) : bool
	{
		return Arrayify::hasKey($name, $this->tags);
	}

	/**
	 * Check whether content contains shortcode.
	 *
	 * @access public
	 * @param string $content
	 * @param string $name
	 * @return bool
	 */
	public function contain(string $content, string $name) : bool
	{
		if ( strpos($content, '[') === false ) {
			return false;
		}

		if ( $this->has($name) ) {

			$regex = "/{$this->getShortcodeRegex()}/s";
			Stringify::matchAll($regex, $content, $matches, 2);
			if ( empty($matches) ) {
				return false;
			}

			foreach ($matches as $shortcode) {
				if ( $name === $shortcode[2] ) {
					return true;
				}
				if ( !empty($shortcode[5]) && $this->contain($shortcode[5], $name) ) {
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
	 * @param string $content
	 * @param bool $escape
	 * @return mixed
	 */
	public function do(string $content, bool $escape = false) : mixed
	{
		if ( $escape ) {
			$content = Sanitizer::escapeHTML($content);
		}

		if ( empty($this->tags) || !TypeCheck::isArray($this->tags) ) {
			return $content;
		}

		$regex = $this->getShortcodeRegex();
		$callback = [$this, 'doShortcodeTag'];

		return Stringify::replaceRegexCb("/{$regex}/s", $callback, $content);
	}

	/**
	 * Get shortcode regex.
	 *
	 * @access public
	 * @return string
	 */
	public function getShortcodeRegex() : string
	{
		$names = array_keys($this->tags);
		$regex = implode('|', array_map('preg_quote', $names));

		return
			'\\['
			. '(\\[?)'
			. "($regex)"
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
	public function parseAtts($content) : mixed
	{
		$atts = [];
		$regex = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
		$content = Stringify::replaceRegex("/[\x{00a0}\x{200b}]+/u", ' ', $content);

		if ( Stringify::matchAll($regex, $content, $match, 2) ) {
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
		if ( TypeCheck::isArray($atts) && count($atts) == 1 ) {
			$key = Arrayify::key($atts);
			if ( TypeCheck::isInt($key) ) {
				$atts = $atts[$key];
			}
		}

		return $atts;
	}

	/**
	 * Get default shortcode attributes.
	 *
	 * @access public
	 * @param array $default
	 * @param array $atts
	 * @param string $name
	 * @return mixed
	 */
	public function getAtts(array $default, array $atts, string $name = '') : mixed
	{
		$atts = (array)$atts;
		$out = [];

		foreach ($default as $key => $default) {
			if ( Arrayify::hasKey($key, $atts) ) {
				$out[$key] = $atts[$key];

			} else {
				$out[$key] = $default;
			}
		}

		if ( $name ) {
			$callback = [$this, "shortcode-atts-{$name}"];
			$out = $this->applyFilter($callback, $out, $default, $atts);
		}

		return $out;
	}

	/**
	 * Remove all shortcodes from content.
	 *
	 * @access public
	 * @param mixed $content
	 * @return mixed
	 */
	public function strip($content) : mixed
	{
		if ( empty($this->tags) || !TypeCheck::isArray($this->tags) ) {
			return $content;
		}

		$regex = $this->getShortcodeRegex();
		$callback = [self::class, 'stripShortcodeTag'];
		return Stringify::replaceRegexCb("/$regex/s", $callback, $content);
	}

	/**
	 * Do shortcode hook tag.
	 *
	 * @access public
	 * @param array $tag
	 * @return mixed
	 * @todo
	 */
	public function doShortcodeTag(array $tag) : mixed
	{
		if ( count($tag) < 7 ) {
			return null;
		}

		$first = $tag[1] ?? '';
		$last = $tag[6] ?? '';

		// Allow [[foo]] syntax for escaping a tag
		if ( $first == '[' && $last == ']' ) {
			return substr($tag[0], 1, -1);
		}

		$name = $tag[2] ?? '';
		$atts = $tag[3] ?? '';
		$atts = $this->parseAtts($atts);

		// Set callback
		$callback = $this->tags[$name] ?? function () {
			return 'error';
		};

		// enclosing tag - extra parameter
		$open = $tag[5] ?? false;
		if ( $open ) {
			return $first . self::callUserFunction($callback, $atts, $open, $tag) . $last;
		}

		// self-closing tag
		$close = $tag[5] ?? false;
		if ( $close ) {
			return $first . self::callUserFunction($callback, $atts, null, $tag) . $last;
		}

		return self::callUserFunction($callback, $atts, null, $name);
	}

	/**
	 * Strip shortcode by tag.
	 *
	 * @access public
	 * @param string $tag
	 * @return string
	 */
	public static function stripShortcodeTag(string $tag) : string
	{
		if ( $tag[1] == '[' && $tag[6] == ']' ) {
			return substr($tag[0], 1, -1);
		}
		return "{$tag[1]}{$tag[6]}";
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
		$callback = function ($match) {
			$content = self::spin($match[1]);
			$parts = explode('|', $content);
			$rand = mt_rand(0, count($parts) - 1);
			return $parts[$rand];
		};

		$value = Stringify::replaceRegexCb(self::SPIN, $callback, $content);
		return $value ?: $content;
	}
}
