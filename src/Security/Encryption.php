<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Security Component
 * @version    : 1.0.2
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2023 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Security;

use FloatPHP\Classes\Filesystem\Stringify;

/**
 * Built-in encryption class,
 * @see JWT for external use is recommended.
 */
class Encryption
{
	/**
	 * @access private
	 * @var SECRET Default passphrase
	 * @var VECTOR Default initialzation vector
	 */
	private const SECRET = 'v6t1pQ97JS';
	private const VECTOR = 'XRtvQPlFs';

	/**
	 * @access private
	 * @var string $data
	 * @var string $key, Secret key (Passphrase)
	 * @var string $vector, Initialzation vector
	 * @var int $length, Encryption length
	 * @var string $prefix, Encryption prefix
	 * @var int $options, Openssl options
	 * @var string $algo, Hash algorithm
	 * @var string $cipher, Openssl cipher algorithm
	 */
	private $data;
	private $key;
	private $vector;
	private $length;
	private $prefix = '[floatcrypt]';
	private $options = 0;
	private $algo = 'sha256';
	private $cipher = 'AES-256-CBC';

	/**
	 * @param string $data
	 * @param string $vector
	 * @param string $key
	 */
	public function __construct($data, $key = self::SECRET, $vector = self::VECTOR, $length = 16)
	{
		$this->data = $data;
		$this->setSecretKey($key);
		$this->setInitVector($vector);
		$this->setLength($length);
		$this->initialize();
	}

	/**
	 * Set secret key (Passphrase).
	 * 
	 * @access public
	 * @param string $key
	 * @param object
	 */
	public function setSecretKey(string $key) : self
	{
		$this->key = $key;
		return $this;
	}

	/**
	 * Set initialzation vector.
	 * 
	 * @access public
	 * @param string $vector
	 * @param object
	 */
	public function setInitVector(string $vector) : self
	{
		$this->vector = $vector;
		return $this;
	}

	/**
	 * Set encryption length.
	 * 
	 * @access public
	 * @param int $length
	 * @param object
	 */
	public function setLength(int $length) : self
	{
		$this->length = $length;
		return $this;
	}

	/**
	 * Set openssl cipher algorithm.
	 * 
	 * @access public
	 * @param string $cipher
	 * @param object
	 */
	public function setCipher(string $cipher) : self
	{
		$this->cipher = $cipher;
		return $this;
	}

	/**
	 * Set openssl options.
	 * 
	 * @access public
	 * @param int $options
	 * @param object
	 */
	public function setOptions(int $options = OPENSSL_ZERO_PADDING) : self
	{
		$this->options = $options;
		return $this;
	}

	/**
	 * Set encryption prefix.
	 * 
	 * @access public
	 * @param string $prefix
	 * @param object
	 */
	public function setPrefix(string $prefix) : self
	{
		$this->prefix = $prefix;
		return $this;
	}

	/**
	 * Encrypt data using base64 loop.
	 * 
	 * @access public
	 * @param int $loop, base64 loop
	 * @param string
	 */
	public function encrypt(int $loop = 1) : string
	{
		$encrypt = openssl_encrypt(
			$this->data,
			$this->cipher,
			$this->key,
			$this->options,
			$this->vector
		);
		$crypted = Tokenizer::base64($encrypt, $loop);
		return "{$this->prefix}{$crypted}";
	}

	/**
	 * Decrypt data using base64 loop.
	 * 
	 * @access public
	 * @param int $loop, base64 loop
	 * @param string
	 */
	public function decrypt(int $loop = 1) : string
	{
		$decrypted = Stringify::replace($this->prefix, '', $this->data);
		return openssl_decrypt(
			Tokenizer::unbase64($decrypted, $loop),
			$this->cipher,
			$this->key,
			$this->options,
			$this->vector
		);
	}

	/**
	 * Check data is crypted using prefix.
	 * 
	 * @access public
	 * @param void
	 * @param bool
	 */
	public function isCrypted() : bool
	{
		return (substr($this->data, 0, strlen($this->prefix)) === $this->prefix);
	}

	/**
	 * Initialize hash.
	 * 
	 * @access protected
	 * @param void
	 * @return object
	 */
	protected function initialize() : self
	{
		$this->key = hash($this->algo, $this->key);
		$this->vector = substr(hash($this->algo, $this->vector), 0, $this->length);
		return $this;
	}
}
