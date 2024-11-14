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

use FloatPHP\Classes\Filesystem\Json;

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
	 * @return void
	 */
	public static function set(string $message, $content = [], string $status = 'success', int $code = 200) : never
	{
		self::setHttpHeaders($code);
		echo Json::encode([
			'status'  => $status,
			'code'    => $code,
			'message' => $message,
			'content' => $content
		]);
		die();
	}

	/**
	 * Set HTTP response header.
	 *
	 * @access public 
	 * @param int $code
	 * @param string $type
	 * @return void
	 */
	public static function setHttpHeaders(int $code, string $type = self::TYPE) : void
	{
		$status = self::getMessage($code);
		$protocol = Server::get('server-protocol');
		header("Content-Type: {$type}");
		header("{$protocol} {$code} {$status}");
	}
}
