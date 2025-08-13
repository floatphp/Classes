<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Filesystem\Json;

/**
 * Advanced HTTP response manipulation.
 */
final class Response extends Status
{
	/**
	 * @access public
	 * @param string TYPE default type
	 */
	public const TYPE = 'application/json';

	/**
	 * Set HTTP response.
	 *
	 * @param string $message
	 * @param mixed $content
	 * @param string $status
	 * @param int $code
	 * @return never
	 */
	public static function set(string $message, $content = [], string $status = 'success', int $code = 200) : never
	{
		self::setHttpHeader($code);
		echo Json::encode([
			'status'  => $status,
			'code'    => $code,
			'message' => $message,
			'content' => $content
		]);
		exit();
	}

	/**
	 * Set HTTP response header.
	 *
	 * @access public 
	 * @param int $code
	 * @param string $type
	 * @return void
	 */
	public static function setHttpHeader(int $code, string $type = self::TYPE) : void
	{
		$status = self::getMessage($code);
		$protocol = Server::get('server-protocol');
		header("Content-Type: {$type}");
		header("{$protocol} {$code} {$status}");
	}
}
