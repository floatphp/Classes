<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Security Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file is a part of FloatPHP Framework.
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
	 * Generate encrypted token.
	 * 
	 * @access public
	 * @param string $user
	 * @param string $pswd
	 * @param string $prefix
	 * @return array
	 */
	public static function get(string $user, string $pswd, ?string $prefix = null) : array
	{
		// Input validation
		if ( trim($user) === '' || trim($pswd) === '' ) {
			throw new \InvalidArgumentException('Username and password cannot be empty');
		}

		try {
			$secret = self::getUniqueId();
			$token = trim("{user:{$user}}{pswd:{$pswd}}");

			$encryption = new Encryption($token, $secret);
			$encryption->setPrefix((string)$prefix);

			$encrypted = $encryption->encrypt();

			// Verify encryption was successful
			if ( $encrypted === false || $encrypted === null ) {
				throw new \RuntimeException('Token encryption failed');
			}

			return [
				'public' => $encrypted,
				'secret' => $secret
			];

		} catch (\Exception $e) {
			throw new \RuntimeException('Token generation failed: ' . $e->getMessage());
		}
	}

	/**
	 * Decrypt and validate token.
	 * 
	 * @access public
	 * @param string $public
	 * @param string $secret
	 * @param string $prefix
	 * @return mixed
	 */
	public static function match(string $public, string $secret, ?string $prefix = null)
	{
		// Input validation
		if ( trim($public) === '' || trim($secret) === '' ) {
			return false;
		}

		$pattern = '/{user:(.*?)}{pswd:(.*?)}/';

		try {
			$encryption = new Encryption($public, $secret);
			$encryption->setPrefix((string)$prefix);
			$access = $encryption->decrypt();

			// Handle failed decryption
			if ( $access === false || $access === null ) {
				return false;
			}

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

		} catch (\Exception $e) {
			// Handle encryption/decryption errors gracefully
			return false;
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
		// Input validation for length
		if ( $length < 1 ) {
			throw new \InvalidArgumentException('Token length must be at least 1');
		}
		if ( $length > 1024 ) {
			throw new \InvalidArgumentException('Token length too large (max: 1024)');
		}

		return bin2hex(random_bytes($length));
	}

	/**
	 * Multi-level base64 encoding.
	 *
	 * @access public
	 * @param string $value
	 * @param int $loop
	 * @return string
	 */
	public static function base64(string $value, int $loop = 1) : string
	{
		// Input validation
		if ( trim($value) === '' ) {
			throw new \InvalidArgumentException('Value cannot be empty');
		}
		if ( $loop < 1 || $loop > 5 ) {
			throw new \InvalidArgumentException('Loop count must be between 1 and 5');
		}

		$encode = base64_encode($value);
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
		// Input validation
		if ( trim($value) === '' ) {
			throw new \InvalidArgumentException('Value cannot be empty');
		}
		if ( $loop < 1 || $loop > 5 ) {
			throw new \InvalidArgumentException('Loop count must be between 1 and 5');
		}

		$decode = base64_decode($value);
		for ($i = 1; $i < $loop; $i++) {
			$decode = base64_decode($decode);
		}
		return $decode;
	}

	/**
	 * Generate unique identifier.
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
	 * Generate UUID v4.
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
	 * Secure random number in range.
	 *
	 * @access public
	 * @param int $min
	 * @param int $max
	 * @return int
	 */
	public static function range(int $min = 5, int $max = 10) : int
	{
		// Input validation for negative values
		if ( $min < 0 || $max < 0 ) {
			throw new \InvalidArgumentException('Range values must be non-negative');
		}

		// Edge case handling for extreme inputs
		if ( $max - $min > PHP_INT_MAX / 2 ) {
			throw new \InvalidArgumentException('Range too large to process safely');
		}

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
	 * Hash values with salt.
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
	 * Verify hashed values.
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
