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
 * This file is a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Server\System;
use FloatPHP\Classes\Filesystem\{TypeCheck, Validator, Arrayify};
use \InvalidArgumentException;

/**
 * Advanced cookie manipulation with enhanced security features.
 */
final class Cookie
{
	/**
	 * @access public
	 * @var array OPTIONS Default secure cookie options
	 */
	public const OPTIONS = [
		'expires'  => 0,
		'path'     => '/',
		'domain'   => '',
		'secure'   => true,
		'httponly' => true,
		'samesite' => 'Strict'
	];

	/**
	 * @access public
	 * @var array SAMESITE Valid SameSite attribute values
	 */
	public const SAMESITE = ['Strict', 'Lax', 'None'];
	
	/**
	 * Get _COOKIE value with validation.
	 * 
	 * @access public
	 * @param string $key
	 * @param bool $validate Whether to validate cookie value
	 * @return mixed
	 */
	public static function get(?string $key = null, bool $validate = true) : mixed
	{
		if ( $key ) {
			if ( !self::isSetted($key) ) {
				return null;
			}
			
			$value = $_COOKIE[$key];
			
			if ( $validate && !Validator::isCookieValue($value) ) {
				return null;
			}
			
			return $value;
		}
		
		if ( !self::isSetted() ) {
			return null;
		}
		
		if ( $validate ) {
			// Return only validated cookies
			$validated = [];
			foreach ($_COOKIE as $name => $value) {
				if ( Validator::isCookieValue($value) ) {
					$validated[$name] = $value;
				}
			}
			return $validated;
		}
		
		return $_COOKIE;
	}

	/**
	 * Set _COOKIE value with enhanced security options.
	 * 
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @param array $options Cookie options
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public static function set(string $key, mixed $value = '', array $options = []) : bool
	{
		// Validate cookie name
		if ( !Validator::isCookieName($key) ) {
			throw new InvalidArgumentException('Invalid cookie name');
		}
		
		// Convert value to string and validate
		$value = (string)$value;
		if ( !Validator::isCookieValue($value) ) {
			throw new InvalidArgumentException('Invalid cookie value');
		}
		
		// Merge with secure defaults
		$options = self::mergeOptions($options);
		
		// Validate options
		self::validateOptions($options);
		
		return setcookie($key, $value, $options);
	}

	/**
	 * Set secure cookie with recommended security settings.
	 * 
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @param int $expires Expiration time (0 for session cookie)
	 * @param string $path Cookie path
	 * @param string $domain Cookie domain
	 * @return bool
	 */
	public static function setSecure(string $key, mixed $value = '', int $expires = 0, string $path = '/', string $domain = '') : bool
	{
		$options = [
			'expires'  => $expires,
			'path'     => $path,
			'domain'   => $domain,
			'secure'   => true,
			'httponly' => true,
			'samesite' => 'Strict'
		];
		
		return self::set($key, $value, $options);
	}

	/**
	 * Delete/expire _COOKIE value.
	 * 
	 * @access public
	 * @param string $key
	 * @param string $path Cookie path
	 * @param string $domain Cookie domain
	 * @return bool
	 */
	public static function delete(string $key, string $path = '/', string $domain = '') : bool
	{
		if ( self::isSetted($key) ) {
			unset($_COOKIE[$key]);
			return setcookie($key, '', [
				'expires' => time() - 3600,
				'path'    => $path,
				'domain'  => $domain
			]);
		}
		return false;
	}

	/**
	 * Check _COOKIE value.
	 * 
	 * @access public
	 * @param string $key
	 * @return bool
	 */
	public static function isSetted(?string $key = null) : bool
	{
		if ( $key ) {
			return isset($_COOKIE[$key]);
		}
		return isset($_COOKIE) && !empty($_COOKIE);
	}

	/**
	 * Unset _COOKIE value.
	 * 
	 * @access public
	 * @param string $key
	 * @return void
	 */
	public static function unset(?string $key = null) : void
	{
		if ( $key ) {
			unset($_COOKIE[$key]);

		} else {
			$_COOKIE = [];
		}
	}

	/**
	 * Clear session cookie.
	 * 
	 * @access public
	 * @return bool
	 */
	public static function clear() : bool
	{
		if ( System::getIni('session.use-cookies') ) {
			$params = session_get_cookie_params();
			self::set(Session::getName(), '', [
				'expires'  => time() - 42000,
				'path'     => $params['path'],
				'domain'   => $params['domain'],
				'secure'   => $params['secure'],
				'httponly' => $params['httponly'],
				'samesite' => $params['samesite']
			]);
			return true;
		}
		return false;
	}

	/**
	 * Get all cookies with security information.
	 * 
	 * @access public
	 * @return array
	 */
	public static function getAll() : array
	{
		$cookies = [];
		$params = session_get_cookie_params();
		
		foreach ($_COOKIE as $name => $value) {
			$cookies[$name] = [
				'value'    => $value,
				'secure'   => $params['secure'] ?? false,
				'httponly' => $params['httponly'] ?? false,
				'samesite' => $params['samesite'] ?? '',
				'valid'    => Validator::isCookieValue($value)
			];
		}
		
		return $cookies;
	}



	/**
	 * Merge options with secure defaults.
	 * 
	 * @access private
	 * @param array $options
	 * @return array
	 */
	private static function mergeOptions(array $options) : array
	{
		// Use secure defaults
		$defaults = self::OPTIONS;
		
		// Auto-detect secure if not explicitly set
		if ( !isset($options['secure']) && Server::isSsl() ) {
			$defaults['secure'] = true;
		}
		
		return Arrayify::merge($defaults, $options);
	}

	/**
	 * Validate cookie options.
	 * 
	 * @access private
	 * @param array $options
	 * @return void
	 * @throws InvalidArgumentException
	 */
	private static function validateOptions(array $options) : void
	{
		// Validate SameSite
		if ( isset($options['samesite']) && !Arrayify::inArray($options['samesite'], self::SAMESITE) ) {
			throw new InvalidArgumentException('Invalid SameSite value. Must be: Strict, Lax, or None');
		}
		
		// Validate secure + SameSite=None combination
		if ( isset($options['samesite']) && $options['samesite'] === 'None' && empty($options['secure']) ) {
			throw new InvalidArgumentException('SameSite=None requires Secure attribute');
		}
		
		// Validate expires
		if ( isset($options['expires']) && !TypeCheck::isInt($options['expires']) && $options['expires'] < 0 ) {
			throw new InvalidArgumentException('Invalid expires value');
		}
	}
}
