<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Security Component
 * @version    : 1.4.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Security;

use FloatPHP\Classes\Filesystem\{Stringify, TypeCheck};

/**
 * Built-in tokenizer class,
 * JWT is recommended for external use.
 */
class Tokenizer
{
	/**
	 * Get token.
	 * 
	 * @access public
	 * @param string $user
	 * @param string $pswd
	 * @param string $prefix
	 * @return array
	 */
	public static function get(string $user, string $pswd, ?string $prefix = null) : array
	{
		$secret = self::getUniqueId();
		$token = trim("{user:{$user}}{pswd:{$pswd}}");

		$encryption = new Encryption($token, $secret);
		$encryption->setPrefix((string)$prefix);

		return [
			'public' => $encryption->encrypt(),
			'secret' => $secret
		];
	}

	/**
	 * Match token.
	 * 
	 * @access public
	 * @param string $public
	 * @param string $secret
	 * @param string $prefix
	 * @return mixed
	 */
	public static function match(string $public, string $secret, ?string $prefix = null)
	{
		$pattern = '/{user:(.*?)}{pswd:(.*?)}/';

		$encryption = new Encryption($public, $secret);
		$encryption->setPrefix((string)$prefix);
		$access = $encryption->decrypt();

		Stringify::match($pattern, $access, $matches);
		$user = $matches[1] ?? false;

		Stringify::match($pattern, $access, $matches);
		$pswd = $matches[2] ?? false;

		if ( $user && $pswd ) {
			return [
				'username' => $user,
				'password' => $pswd
			];
		}

		return false;
	}

	/**
	 * Generate random token.
	 *
	 * @access public
	 * @param int $length
	 * @return string
	 */
	public static function generate(int $length = 32) : string
	{
		return bin2hex(random_bytes($length));
	}

	/**
	 * Encode base64.
	 *
	 * @access public
	 * @param string $value
	 * @param int $loop
	 * @return string
	 */
	public static function base64(string $value, int $loop = 1) : string
	{
		$encode = base64_encode($value);
		$loop = ($loop > 5) ? 5 : $loop;
		for ($i = 1; $i < $loop; $i++) {
			$encode = base64_encode($encode);
		}
		return $encode;
	}

	/**
	 * Decode base64.
	 *
	 * @access public
	 * @param string $value
	 * @param int $loop
	 * @return string
	 */
	public static function unbase64(string $value, int $loop = 1) : string
	{
		$decode = base64_decode($value);
		$loop = ($loop > 5) ? 5 : $loop;
		for ($i = 1; $i < $loop; $i++) {
			$decode = base64_decode($decode);
		}
		return $decode;
	}

	/**
	 * Get unique Id.
	 *
	 * @access public
	 * @param bool $md5
	 * @return string
	 */
	public static function getUniqueId(bool $md5 = true) : string
	{
		$id = uniqid((string)time());
		return ($md5) ? md5($id) : $id;
	}

	/**
	 * Get UUID (4).
	 *
	 * @access public
	 * @param bool $format
	 * @return string
	 */
	public static function getUuid(bool $format = false) : string
	{
		$uuid = sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff)
		);

		if ( $format ) {
			$uuid = Stringify::remove('-', $uuid);
		}

		return $uuid;
	}

	/**
	 * Get range of numbers.
	 *
	 * @access public
	 * @param int $min
	 * @param int $max
	 * @return int
	 */
	public static function range(int $min = 5, int $max = 10) : int
	{
		if ( $min >= $max ) {
			return $min;
		}

		$range = $max - $min;
		$log = log($range, 2);
		$bytes = (int)ceil($log / 8);
		$bits = (int)ceil($log) + 1;
		$filter = (1 << $bits) - 1;

		do {
			$pseudo = openssl_random_pseudo_bytes($bytes);
			$rand = hexdec(bin2hex($pseudo));
			$rand = $rand & $filter;
		} while ($rand >= $range);

		return $min + $rand;
	}

	/**
	 * Hash value.
	 *
	 * @access public
	 * @param mixed $value
	 * @param string $salt
	 * @return string
	 */
	public static function hash($value, string $salt = 'Y3biC') : string
	{
		if ( !TypeCheck::isString($value) ) {
			$value = Stringify::serialize($value);
		}

		$value = "{$salt}{$value}";
		return hash('sha256', $value);
	}

	/**
	 * Hash value.
	 *
	 * @access public
	 * @param string $hash
	 * @param mixed $value
	 * @param string $salt
	 * @return bool
	 */
	public static function verify(string $hash, $value, string $salt = 'Y3biC') : bool
	{
		$value = self::hash($value, $salt);
		return hash_equals($value, $hash);
	}
}
