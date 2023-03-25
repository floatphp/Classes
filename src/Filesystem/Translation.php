<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.0.2
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2023 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

/**
 * Built-in Translation Class for FloatPHP,
 * @see https://develdocs.phpmyadmin.net/motranslator/PhpMyAdmin/MoTranslator.html
 */
class Translation
{
	/**
	 * @access protected
	 * @var int $byteOrder
	 * @var string $pluralHeader
	 * @var array $tableOriginals
	 * @var array $tableTranslations
	 * @var int $position
	 * @var string $mo
	 * @var array $count
	 * @var bool $canTranslate
	 */
	protected $byteOrder = 0;
	protected $pluralHeader = null;
	protected $tableOriginals = null;
	protected $tableTranslations = null;
	protected $position = 0;
	protected $mo;
	protected $count = [];
	protected $canTranslate = false;

	/**
	 * @param string $locale
	 * @param string $path
	 */
	public function __construct($locale = '', $path = '')
	{
		if ( $this->load($locale,$path) ) {
			$magic = $this->read(4);
			if ( $magic == "\x95\x04\x12\xde" ) {
				$this->byteOrder = 1;

			} elseif ( $magic == "\xde\x12\x04\x95" ) {
				$this->byteOrder = 0;
			}
			$this->initCount();
		}
	}

	/**
	 * Translates a string.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public function translate($string = '') : string
	{
		if ( $this->canTranslate ) {
			if ( !empty($string) ) {
				return $this->gettext($string);
			}
		}
		return (string)$string;
	}

	/**
	 * Translates a string.
	 *
	 * @access public
	 * @param string $string
	 * @return string|false
	 */
	public function gettext($string = '')
	{
		if ( $this->canTranslate ) {
			$this->loadTables();
			$num = $this->findString($string);
			if ( $num == -1 ) {
				return $string;

			} else {
				return $this->getTranslationString($num);
			}
		}
		return false;
	}

  	/**
  	 * Plural version of gettext.
  	 *
  	 * @access public
  	 * @param string single
  	 * @param string plural
  	 * @param string number
  	 * @return string|false
  	 */
  	public function ngettext($single, $plural, $number)
  	{
  		if ( $this->canTranslate ) {
	    	$select = $this->selectString($number);
	    	$key = $single . chr(0) . $plural;
			$num = $this->findString($key);
			if ( $num == -1 ) {
				return ($number !== 1) ? $plural : $single;

			} else {
				$result = $this->getTranslationString($num);
				$list = explode(chr(0),$result);
				return $list[$select];
			}
		}
		return false;
  	}

	/**
	 * Plural version of gettext.
	 *
	 * @access public
	 * @param string context
	 * @param string msgid
	 * @return string|false
	 */
	public function pgettext($context, $msgid)
	{
		if ( $this->canTranslate ) {
			$key = $context . chr(4) . $msgid;
			$ret = $this->translate($key);
			if ( strpos($ret,"\004") !== false ) {
				return $msgid;

			} else {
				return $ret;
			}
		}
		return false;
	}

	/**
	 * Plural version of gettext.
	 *
	 * @access public
	 * @param string context
	 * @param string singular
	 * @param string plural
	 * @param int number
	 * @return string|false
	 */
	public function npgettext($context, $singular, $plural, $number)
	{
		if ( $this->canTranslate ) {
			$key = $context . chr(4) . $singular;
			$ret = $this->ngettext($key,$plural,$number);
			if ( strpos($ret, "\004") !== false ) {
				return $singular;

			} else {
				return $ret;
			}
		}
		return false;
	}

	/**
	 * Init 32 bit integer from stream.
	 *
	 * @access protected
	 * @param void
	 * @return void
	 */
	protected function initCount()
	{
		// Revision
		$this->readInt(); 

		// Init
		$this->count['total'] = $this->readInt();
		$this->count['original'] = $this->readInt();
		$this->count['translation'] = $this->readInt();
	}

	/**
	 * Load mo file with given local.
	 *
	 * @access protected
	 * @param string $locale
	 * @param string $path
	 * @return bool
	 */
	protected function load($locale, $path) : bool
	{
	    if ( File::exists(($file = "{$path}/{$locale}.mo")) ) {
			$this->mo = fopen($file, 'rb');
			return $this->canTranslate = true;
		}
		return false;
	}

	/**
	 * Read 32 bit integer from stream.
	 *
	 * @access protected
	 * @param void
	 * @return int
	 */
	protected function readInt() : int
	{
	    if ( $this->byteOrder == 0 ) {
			// Low endian
			$input = unpack('V', $this->read(4));
			return Arrayify::shift($input);

	    } else {
			// Big endian
			$input = unpack('N', $this->read(4));
			return Arrayify::shift($input);
	    }
	}

	/**
	 * Read bytes.
	 *
	 * @access protected
	 * @param int $bytes
	 * @return string
	 */
	protected function read($bytes) : string
	{
		$data = '';
	    if ( $bytes ) {
			fseek($this->mo,$this->position);
			while ($bytes > 0) {
				$chunk = fread($this->mo, $bytes);
				$data .= $chunk;
				$bytes -= strlen($chunk);
			}
			$this->position = ftell($this->mo);
	    }
	    return $data;
	}

	/**
	 * Read array of integers from stream.
	 *
	 * @access protected
	 * @param int $count
	 * @return array
	 */
	protected function readIntArray($count) : array
	{
		if ( $this->byteOrder == 0 ) {
			// low endian
			return unpack("V{$count}", $this->read(4 * $count));

	    } else {
			// big endian
			return unpack("N{$count}", $this->read(4 * $count));
	    }
	}

	/**
	 * Load the translations tables from the Mo file.
	 *
	 * @access protected
	 * @param void
	 * @return void
	 */
	protected function loadTables()
	{
		if ( TypeCheck::isArray($this->tableOriginals) 
		  && TypeCheck::isArray($this->tableTranslations) ) {
			return;
		}
	  	if ( !TypeCheck::isArray($this->tableOriginals) ) {
			$this->setPosition($this->count['original']);
			$this->tableOriginals = $this->readIntArray($this->count['total'] * 2);
	  	}
		if ( !TypeCheck::isArray($this->tableTranslations) ) {
			$this->setPosition($this->count['translation']);
			$this->tableTranslations = $this->readIntArray($this->count['total'] * 2);
		}
	}

	/**
	 * Return string from originals table.
	 *
	 * @access protected
	 * @param int $num
	 * @return string
	 */
	protected function getOriginalString($num) : string
	{
		$length = $this->tableOriginals[$num * 2 + 1];
		$offset = $this->tableOriginals[$num * 2 + 2];
		if ( !$length ) {
			return '';
		}
		$this->setPosition($offset);
		$data = $this->read($length);
		return (string)$data;
	}

	/**
	 * Return string from translations table.
	 *
	 * @access protected
	 * @param int $num
	 * @return string
	 */
	protected function getTranslationString($num) : string
	{
		$length = $this->tableTranslations[$num * 2 + 1];
		$offset = $this->tableTranslations[$num * 2 + 2];
		if ( !$length ) {
			return '';
		}
		$this->setPosition($offset);
		$data = $this->read($length);
		return (string)$data;
	}

	/**
	 * Binary search for string.
	 *
	 * @access protected
	 * @param string $string
	 * @param int $start
	 * @param int $end
	 * @return mixed
	 */
	protected function findString($string, $start = -1, $end = -1)
	{
		if ( ($start == -1) || ($end == -1) ) {
		  $start = 0;
		  $end = $this->count['total'];
		}
		if ( abs($start - $end) <= 1 ) {
			$txt = $this->getOriginalString($start);
			if ( $string == $txt ) {
				return $start;

			} else {
				return -1;
			}

		} elseif ($start > $end) {
			return $this->findString($string, $end, $start);

		} else {
			$half = (int)(($start + $end) / 2);
			$cmp = strcmp($string, $this->getOriginalString($half));
			if ( $cmp == 0 ) {
				return $half;

			} elseif ( $cmp < 0 ) {
				return $this->findString($string, $start, $half);
				
			} else {
				return $this->findString($string, $half, $end);
			}
		}
	}

	/**
	 * Sanitize plural form expression for use in PHP eval call.
	 *
	 * @access protected
	 * @param string $exp
	 * @return string
	 */
	protected function sanitizePluralExpression($exp) : string
	{
		$exp = Stringify::replaceRegex(
			'@[^a-zA-Z0-9_:;\(\)\?\|\&=!<>+*/\%-]@',
			'',
			$exp
		) . ';';
		$res = '';
		$p = 0;
		for ($i = 0; $i < strlen($exp); $i++) {
			$ch = $exp[$i];
			switch ($ch) {
				case '?':
					$res .= ' ? (';
					$p++;
					break;
				case ':':
					$res .= ') : (';
					break;
				case ';':
					$res .= str_repeat(')',$p) . ';';
					$p = 0;
					break;
				default:
					$res .= $ch;
			}
		}
		return $res;
	}

	/**
	 * Parse full PO header and extract only plural forms line.
	 *
	 * @access protected
	 * @param string $header
	 * @return string
	 */
	protected function extractPluralForms($header) : string
	{
		if ( ($regs = Stringify::match("/(^|\n)plural-forms: ([^\n]*)\n/i",$header,-1)) ) {
			$exp = $regs[2];
		} else {
			$exp = "nplurals=2; plural=n == 1 ? 0 : 1;";
		}
		return $exp;
	}

	/**
	 * Get possible plural forms from Mo header.
	 *
	 * @access protected
	 * @param void
	 * @return string
	 */
	protected function getPluralForms() : string
	{
		$this->loadTables();
		if ( !TypeCheck::isString($this->pluralHeader) ) {
			$header = $this->getTranslationString(0);
			$expr = $this->extractPluralForms($header);
			$this->pluralHeader = $this->sanitizePluralExpression($expr);
		}
		return $this->pluralHeader;
	}

	/**
	 * Detect which plural form to take.
	 *
	 * @access protected
	 * @param int $n
	 * @return int|array
	 */
	protected function selectString($n)
	{
		$string = $this->getPluralForms();
		$string = Stringify::replace('nplurals', "\$total", $string);
		$string = Stringify::replace("n", (int)$n, $string);
		$string = Stringify::replace('plural', "\$plural", $string);
		$total = 0;
		$plural = 0;
		eval("$string");
		if ( $plural >= $total ) {
			$plural = $total - 1;
		}
		return $plural;
	}

  	/**
  	 * Set position.
  	 *
  	 * @access protected
  	 * @param int $position
  	 * @return void
  	 */
	protected function setPosition($position)
	{
		fseek($this->mo, $position);
		$this->position = ftell($this->mo);
	}
}
