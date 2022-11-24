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

final class Json extends File
{
	/**
	 * Parse Json file.
	 *
	 * @access public
	 * @param bool $isArray
	 * @return mixed
	 */
	public static function parse($file, $isArray = false)
	{
		return self::decode(self::r($file),$isArray);
	}

	/**
	 * Decode Json.
	 *
	 * @access public
	 * @param string $content
	 * @param bool $isArray
	 * @return mixed
	 */
	public static function decode($content, $isArray = false)
	{
		return json_decode($content,$isArray);
	}

	/**
	 * Encode Json.
	 *
	 * @access public
	 * @param mixen $data
	 * @return string
	 */
	public static function encode($data)
	{
		return json_encode($data);
	}

	/**
	 * Format Json.
	 *
	 * @access public
	 * @param mixen $data
	 * @param int $args
	 * @return string
	 *
	 * JSON_UNESCAPED_UNICODE : 256
	 * JSON_PRETTY_PRINT : 128
	 * JSON_UNESCAPED_SLASHES : 64
	 */
	public static function format($data, $args = 64|256)
	{
		return json_encode($data,$args);
	}
}
