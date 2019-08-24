<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes HTTP Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 */

namespace floatphp\Classes\Http;

class ResponseXML
{
	public static function format($xml)
	{
		$xml = str_replace('<?xml version="1.0" encoding="utf-8" ?>', '', $xml);
		$xml = str_replace('</xml>', '', $xml);
		return $xml;
	}
	public static function parse($xml)
	{
		return simplexml_load_string($xml);
	}
}
