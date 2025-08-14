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
 * Built-in cryptography class,
 * JWT is recommended for external use.
 */
class Encryption
{
	/**
	 * @access public
	 * @var string PREFIX Default encryption prefix
	 */
	public const PREFIX = '[FloatCrypt]';

	/**
	 * @access private
	 * @var string SECRET Default secret key (Passphrase)
	 * @var string VECTOR Default initialzation vector
	 * @var int LENGTH Default encryption vector length
	 * @var string ALGO Default hash algorithm
	 * @var int OPTIONS Default openssl options
	 * @var string CIPHER Default openssl cipher algorithm
	 */
	private const SECRET  = 'v6t1pQ97JS';
	private const VECTOR  = 'XRtvQPlFs';
	private const LENGTH  = 16;
	private const ALGO    = 'sha256';
	private const OPTIONS = 0;
	private const CIPHER  = 'AES-256-CBC';

	/**
	 * @access private
	 * @var mixed $data
	 * @var string $secret, Secret key (Passphrase)
	 * @var string $vector, Initialzation vector
	 * @var int $length, Encryption vector length
	 * @var string $prefix, Encryption prefix
	 * @var int $options, OpenSSL options
	 * @var string $cipher, OpenSSL cipher algorithm
	 * @var bool $asString, Decrypted data as string
	 * @var bool $bypass, Bypass encrypted string
	 */
	private $data;
	private $secret;
	private $vector;
	private $length;
	private $prefix;
	private $options;
	private $cipher;
	private $asString = false;
	private $bypass = false;

	/**
	 * Init encryption (Encrypt / Decrypt).
	 *
	 * @param mixed $data
	 * @param string $secret
	 * @param string $vector
	 * @param int $length
	 */
	public function __construct($data, ?string $secret = self::SECRET, ?string $vector = self::VECTOR, ?int $length = self::LENGTH)
	{
		// Set data
		$this->data = $data;

		// Set secret key
		$this->secret = $secret;

		// Set vector
		$this->vector = $vector;
		$this->length = $length;

		// Initialize
		$this->setPrefix(self::PREFIX);
		$this->setOptions();
		$this->setCipher();
		$this->initialize();
	}

	/**
	 * Set OpenSSL options.
	 *
	 * @access public
	 * @param int $options
	 * @param object
	 */
	public function setOptions(int $options = self::OPTIONS) : self
	{
		$this->options = $options;
		return $this;
	}

	/**
	 * Set OpenSSL cipher algorithm.
	 *
	 * @access public
	 * @param string $options
	 * @param object
	 */
	public function setCipher(string $cipher = self::CIPHER) : self
	{
		$this->cipher = $cipher;
		return $this;
	}

	/**
	 * Set encryption prefix.
	 *
	 * @access public
	 * @param string $prefix
	 * @param object
	 */
	public function setPrefix(?string $prefix = null) : self
	{
		$this->prefix = (string)$prefix;
		return $this;
	}

	/**
	 * Initialize hash.
	 *
	 * @access public
	 * @param string $algo
	 * @return object
	 */
	public function initialize(string $algo = self::ALGO) : self
	{
		$this->secret = hash($algo, $this->secret);
		$this->vector = substr(hash($algo, $this->vector), 0, $this->length);
		return $this;
	}

	/**
	 * Encrypt data.
	 *
	 * @access public
	 * @param int $loop, Base64 loop (Max 5)
	 * @param string
	 */
	public function encrypt(int $loop = 1) : string
	{
		if ( TypeCheck::isString($this->data) ) {
			if ( $this->bypass && $this->isCrypted() ) {
				return $this->data;
			}
		} else {
			$this->data = Stringify::serialize($this->data);
		}

		$loop = ($loop <= 3) ? $loop : 3;
		$encrypt = openssl_encrypt(
			$this->data,
			$this->cipher,
			$this->secret,
			$this->options,
			$this->vector
		);

		$crypted = Tokenizer::base64($encrypt, $loop);
		unset($this->data);

		return "{$this->prefix}{$crypted}";
	}

	/**
	 * Decrypt data.
	 *
	 * @access public
	 * @param int $loop, Base64 loop (Max 5)
	 * @param mixed
	 */
	public function decrypt(int $loop = 1)
	{
		if ( !TypeCheck::isString($this->data) ) {
			return false;
		}

		$loop = ($loop <= 3) ? $loop : 3;
		$crypted = Stringify::remove($this->prefix, $this->data);
		$decrypted = (string)openssl_decrypt(
			Tokenizer::unbase64($crypted, $loop),
			$this->cipher,
			$this->secret,
			$this->options,
			$this->vector
		);

		if ( !$this->asString ) {
			$decrypted = Stringify::unserialize($decrypted);
		}

		unset($this->data);
		return $decrypted;
	}

	/**
	 * Get decrypted data as string.
	 *
	 * @access public
	 * @return object
	 */
	public function asString() : self
	{
		$this->asString = true;
		return $this;
	}

	/**
	 * Bypass encrypted string.
	 *
	 * @access public
	 * @return object
	 */
	public function bypass() : self
	{
		$this->bypass = true;
		return $this;
	}

	/**
	 * Check whether data is crypted using prefix.
	 *
	 * @access public
	 * @param bool
	 */
	public function isCrypted() : bool
	{
		$prefix = substr($this->data, 0, strlen($this->prefix));
		return $prefix === $this->prefix;
	}
}
