<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Filesystem Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Filesystem;

final class Json extends File
{
	/**
	 * @param string $path
	 */
	public function __construct($path)
	{
		parent::__construct($path);
		$this->read();
	}

	/**
	 * Parse JSON object
	 *
	 * @access public
	 * @param bool $isArray
	 * @return mixed
	 */
	public function parse($isArray = false)
	{
		return self::decode($this->getContent(),$isArray);
	}

	/**
	 * Decode JSON
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
	 * Encode JSON
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
	 * Format JSON For WordPress
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
