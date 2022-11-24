<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.0.0
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2022 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Filesystem\Stringify;
use FloatPHP\Classes\Filesystem\Json;
use \stdClass;

final class ResponseXML
{
	/**
	 * @access public 
	 * @param string $xml
	 * @return string
	 */
	public static function format($xml)
	{
		$xml = Stringify::replace('<?xml version="1.0" encoding="utf-8" ?>','',$xml);
		$xml = Stringify::replace('</xml>','',$xml);
		return $xml;
	}
	
	/**
	 * @access public 
	 * @param string $xml
	 * @param int $args
	 * @return string
	 *
	 * LIBXML_NOCDATA: 16384
	 * LIBXML_VERSION: 20908
	 */
	public static function parse($xml, $args = 16384|20908)
	{
		return simplexml_load_string($xml,'SimpleXMLElement',$args);
	}

	/**
	 * @access public 
	 * @param string $xml
	 * @param bool $isArray
	 * @param int $args
	 * @return mixed
	 */
	public static function parseFile($xml, $isArray = false, $args = 16384|20908)
	{
		$object = @simplexml_load_file($xml,'SimpleXMLElement',$args);
		if ( $isArray ) {
			$object = ($object) ? $object : new stdClass;
			$object = Json::decode(Json::encode($object),true);
		}
		return $object;
	}
}
