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

class Response
{
	/**
	 * @access public 
	 * @param string $status, 
	 * @return json
	 */
	public static function set($message = '', $content = [], $status = 'success', $statusCode = 200)
	{
		self::setHttpHeaders($statusCode);
		echo json_encode([
			'status'  => $status,
			'message' => $message,
			'content' => $content
		]);
		die();
	}

	/**
	 * @access public 
	 * @param string $contentType
	 * @param int $statusCode
	 * @return void
	 */
	public static function setHttpHeaders($statusCode, $contentType = 'application/json')
	{
		$statusMessage = Status::getMessage($statusCode);
		header("Content-Type: {$contentType}");
		header("HTTP/1.1 {$statusCode} {$statusMessage}");
	}

	/**
	 * @param string $reponse, boolean|null $array
	 * @return array|object
	 */
	public static function get($reponse, $array = null)
	{
		if ($array) return json_decode( $reponse,true );
		else return json_decode( $reponse );
	}
}
