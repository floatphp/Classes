<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.3.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Filesystem\Stringify;

final class Xml
{
	/**
	 * Parse XML string.
	 *
	 * [NOCDATA: 16384].
	 * [VERSION: 20908].
	 * 
	 * @access public 
	 * @param string $xml
	 * @param int $args
	 * @return mixed
	 */
	public static function parse(string $xml, int $args = 16384 | 20908) : mixed
	{
		return @simplexml_load_string($xml, 'SimpleXMLElement', $args);
	}

	/**
	 * Parse XML file.
	 * 
	 * @access public 
	 * @param string $path
	 * @param int $args
	 * @return mixed
	 */
	public static function parseFile(string $path, int $args = 16384 | 20908) : mixed
	{
		return @simplexml_load_file($path, 'SimpleXMLElement', $args);
	}

	/**
	 * Ignore XML errors.
	 * 
	 * @access public 
	 * @param bool $handling, User errors
	 * @return mixed
	 */
	public static function ignoreErrors(bool $handling = true) : bool
	{
		return libxml_use_internal_errors($handling);
	}

	/**
	 * Format XML string.
	 * 
	 * @access public 
	 * @param string $xml
	 * @return string
	 */
	public static function format(string $xml) : string
	{
		$xml = Stringify::remove('<?xml version="1.0" encoding="utf-8" ?>', $xml);
		$xml = Stringify::remove('</xml>', $xml);
		return $xml;
	}
}
