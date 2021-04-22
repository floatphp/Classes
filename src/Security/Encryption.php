<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Security Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Security;

class Encryption
{
	/**
	 * @access public
	 */
	const VECTOR = 'XRtvQPlFs';
	const SECRET = 'v6t1pQ97JS';

	/**
	 * @access private
	 * @var string $password
	 * @var string $initVector
	 * @var string $secretKey
	 */
	private $password;
	private $initVector;
	private $secretKey;
	private $length;
	private $prefix = '[floatphp]';
	private $method = 'AES-256-CBC';

	/**
	 * @param string $password
	 * @param string $initVector
	 * @param string $secretKey
	 */
	public function __construct($password, $initVector = self::VECTOR, $secretKey = self::SECRET, $length = 16)
	{
		$this->password = $password;
		$this->initVector = $initVector;
		$this->secretKey = $secretKey;
		$this->length = $length;
		$this->initialize();
	}

	/**
	 * @access protected
	 * @param void
	 * @param void
	 */
	protected function initialize()
	{
		$this->secretKey = hash('sha256',$this->secretKey);
		$this->initVector = substr(hash('sha256',$this->initVector),0,$this->length);
	}

	/**
	 * @access public
	 * @param string $method
	 * @param object
	 */
	public function setMethod($method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * @access public
	 * @param string $prefix
	 * @param object
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
		return $this;
	}

	/**
	 * @access public
	 * @param void
	 * @param string
	 */
	public function encrypt()
	{
		$crypted = base64_encode(
			openssl_encrypt($this->password,$this->method,$this->secretKey,0,$this->initVector)
		);
		return "{$this->prefix}{$crypted}";
	}

	/**
	 * @access public
	 * @param void
	 * @param string
	 */
	public function decrypt()
	{
		$decrypted = Stringify::replace($this->prefix,'',$this->password);
		return openssl_decrypt(
			base64_decode($decrypted),$this->method,$this->secretKey,0,$this->initVector
		);
	}

	/**
	 * @access public
	 * @param void
	 * @param bool
	 */
	public function isCrypted()
	{
		return substr($this->password,0,strlen($this->prefix)) === $this->prefix;
	}
}
