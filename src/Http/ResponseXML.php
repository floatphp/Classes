<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Http Component
 * @version   : 1.1.0
 * @category  : PHP framework
 * @copyright : (c) JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace App\System\Classes\Http;

class ResponseXML
{
	/**
	 * @access public 
	 * @param string $status, 
	 * @return string
	 */
	public static function format($xml)
	{
		$xml = str_replace('<?xml version="1.0" encoding="utf-8" ?>', '', $xml);
		$xml = str_replace('</xml>', '', $xml);
		return $xml;
	}
	
	/**
	 * @access public 
	 * @param string $status, 
	 * @return string
	 */
	public static function parse($xml)
	{
		return simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA|LIBXML_VERSION);
	}

	/**
	 * @access public 
	 * @param string $status, 
	 * @return string
	 */
	public static function parseFile($xml)
	{
		return simplexml_load_file($xml, 'SimpleXMLElement', LIBXML_NOCDATA|LIBXML_VERSION);
	}
}
