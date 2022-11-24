<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Security Component
 * @version    : 1.0.0
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2022 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Security;

class Encryption
{
	/**
	 * @access private
	 */
	private const SECRET = 'v6t1pQ97JS';
	private const VECTOR = 'XRtvQPlFs';

	/**
	 * @access private
	 * @var string $data
	 * @var string $initVector
	 * @var string $secretKey
	 * @var int $length
	 * @var int $options
	 * @var string $algorithm
	 * @var string $cipher
	 */
	private $data;
	private $initVector;
	private $secretKey;
	private $length;
	private $options = 0;
	private $algorithm = 'sha256';
	private $cipher = 'AES-256-CBC';

	/**
	 * @param string $data
	 * @param string $initVector
	 * @param string $secretKey
	 */
	public function __construct($data, $secretKey = self::SECRET, $initVector = self::VECTOR, $length = 16)
	{
		$this->data = $data;
		$this->setSecretKey($secretKey);
		$this->setInitVector($initVector);
		$this->setLength($length);
		$this->initialize();
	}

	/**
	 * @access public
	 * @param string $key
	 * @param void
	 */
	public function setSecretKey(string $key)
	{
		$this->secretKey = $key;
	}

	/**
	 * @access public
	 * @param string $vector
	 * @param void
	 */
	public function setInitVector(string $vector)
	{
		$this->initVector = $vector;
	}

	/**
	 * @access public
	 * @param int $length
	 * @param void
	 */
	public function setLength(int $length)
	{
		$this->length = $length;
	}

	/**
	 * @access public
	 * @param string $cipher
	 * @param object
	 */
	public function setCipher(string $cipher) : object
	{
		$this->cipher = $cipher;
		return $this;
	}

	/**
	 * @access public
	 * @param int $options
	 * @param object
	 */
	public function setOptions(int $options = OPENSSL_ZERO_PADDING) : object
	{
		$this->options = $options;
		return $this;
	}

	/**
	 * @access public
	 * @param void
	 * @param object
	 */
	public function initialize() : object
	{
		$this->secretKey = hash($this->algorithm,$this->secretKey);
		$this->initVector = substr(hash($this->algorithm,$this->initVector),0,$this->length);
		return $this;
	}

	/**
	 * @access public
	 * @param int $loop
	 * @param string
	 */
	public function encrypt(int $loop = 1) : string
	{
		$encrypt = openssl_encrypt($this->data,$this->cipher,$this->secretKey,$this->options,$this->initVector);
		return Tokenizer::base64($encrypt,$loop);
	}

	/**
	 * @access public
	 * @param int $loop
	 * @param string
	 */
	public function decrypt($loop = 1) : string
	{
		return openssl_decrypt(
			Tokenizer::unbase64($this->data,$loop),$this->cipher,$this->secretKey,$this->options,$this->initVector
		);
	}
}
