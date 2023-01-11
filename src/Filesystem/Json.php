<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.0.1
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2023 Jihad Sinnaour <mail@jihadsinnaour.com>
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
	 * Parse JSON.
	 *
	 * @access public
	 * @param bool $isArray
	 * @return mixed
	 */
	public static function parse($file, $isArray = false)
	{
		return self::decode(self::r($file), $isArray);
	}

	/**
	 * Decode JSON.
	 *
	 * @access public
	 * @param string $content
	 * @param bool $isArray
	 * @return mixed
	 */
	public static function decode($content, $isArray = false)
	{
		return json_decode((string)$content, (bool)$isArray);
	}

	/**
	 * Encode JSON.
	 *
	 * @access public
	 * @param mixen $value
	 * @return string
	 */
	public static function encode($value)
	{
		return self::format($value, 0);
	}

	/**
	 * Format JSON.
	 * 
	 * JSON_UNESCAPED_UNICODE : 256
	 * JSON_PRETTY_PRINT : 128
	 * JSON_UNESCAPED_SLASHES : 64
	 * 
	 * @access public
	 * @param mixed $value
	 * @param int $flags
	 * @param int $depth
	 * @return mixed
	 */
	public static function format($value, $flags = 64|256, $depth = 512)
	{
		return json_encode($value, $flags, $depth);
	}
}
