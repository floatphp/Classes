<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.0.2
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2023 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
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
	 * @param string $message
	 * @param array $content
	 * @param string $status
	 * @param int $code
	 * @return string
	 */
	public static function set($message = '', $content = [], $status = 'success', $code = 200)
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
	 * @access public 
	 * @param int $code
	 * @param string $type
	 * @return void
	 */
	public static function setHttpHeaders($code, $type = 'application/json')
	{
		$status = self::getMessage($code);
		$protocol = Server::get('server-protocol');
		header("Content-Type: {$type}");
		header("{$protocol} {$code} {$status}");
	}
}
