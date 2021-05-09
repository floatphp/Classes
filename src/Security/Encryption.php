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

use FloatPHP\Classes\Filesystem\Stringify;

class Encryption
{
	/**
	 * @access public
	 */
	const SECRET = 'v6t1pQ97JS';
	const VECTOR = 'XRtvQPlFs';

	/**
	 * @access private
	 * @var string $data
	 * @var string $initVector
	 * @var string $secretKey
	 * @var int $length
	 * @var int $options
	 * @var string $cipher
	 */
	private $data;
	private $initVector;
	private $secretKey;
	private $length;
	private $options = 0;
	private $cipher = 'AES-256-CBC';

	/**
	 * @param string $data
	 * @param string $initVector
	 * @param string $secretKey
	 */
	public function __construct($data, $secretKey = self::SECRET, $initVector = self::VECTOR, $length = 16)
	{
		$this->data = $data;
		$this->secretKey = $secretKey;
		$this->initVector = $initVector;
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
		$this->initVector = substr(hash('sha256',$this->initVector),$this->options,$this->length);
	}

	/**
	 * @access public
	 * @param string $cipher
	 * @param object
	 */
	public function setCipher($cipher)
	{
		$this->cipher = $cipher;
		return $this;
	}

	/**
	 * @access public
	 * @param string $options
	 * @param object
	 */
	public function setOptions($options)
	{
		$this->options = $options;
		return $this;
	}

	/**
	 * @access public
	 * @param void
	 * @param string
	 */
	public function encrypt()
	{
		return base64_encode(
			openssl_encrypt($this->data,$this->cipher,$this->secretKey,$this->options,$this->initVector)
		);
	}

	/**
	 * @access public
	 * @param void
	 * @param string
	 */
	public function decrypt()
	{
		return openssl_decrypt(
			base64_decode($this->data),$this->cipher,$this->secretKey,$this->options,$this->initVector
		);
	}
}
