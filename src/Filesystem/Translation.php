<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file is a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

/**
 * Built-in translation class.
 * @see https://github.com/phpmyadmin/motranslator
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
	 * Init translation.
	 * 
	 * @param string $locale
	 * @param string $path
	 */
	public function __construct(?string $locale = null, string $path = '/')
	{
		if ( $this->load($locale, $path) ) {

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
	 * Translate string.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public function translate(string $string) : string
	{
		if ( $this->canTranslate ) {
			if ( !empty($string) ) {
				return $this->gettext($string);
			}
		}
		return $string;
	}

	/**
	 * Translate string.
	 *
	 * @access public
	 * @param string $string
	 * @return mixed
	 */
	public function gettext(string $string) : mixed
	{
		if ( $this->canTranslate ) {
			$this->loadTables();
			$num = $this->findString($string);
			if ( $num == -1 ) {
				return $string;
			}
			return $this->getTranslationString($num);
		}
		return false;
	}

	/**
	 * Plural version of gettext.
	 *
	 * @access public
	 * @param string single
	 * @param string plural
	 * @param int number
	 * @return mixed
	 */
	public function ngettext(string $single, string $plural, int $number) : mixed
	{
		if ( $this->canTranslate ) {
			$select = $this->selectString($number);
			$key = $single . chr(0) . $plural;
			$num = $this->findString($key);
			if ( $num == -1 ) {
				return ($number !== 1) ? $plural : $single;
			}
			$result = $this->getTranslationString($num);
			$list = explode(chr(0), $result);
			return $list[$select];
		}
		return false;
	}

	/**
	 * Plural version of gettext.
	 *
	 * @access public
	 * @param string context
	 * @param string msgid
	 * @return mixed
	 */
	public function pgettext(string $context, string $msgid) : mixed
	{
		if ( $this->canTranslate ) {
			$key = $context . chr(4) . $msgid;
			$ret = $this->translate($key);
			if ( strpos($ret, "\004") !== false ) {
				return $msgid;
			}
			return $ret;
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
	 * @return mixed
	 */
	public function npgettext(string $context, string $singular, string $plural, int $number) : mixed
	{
		if ( $this->canTranslate ) {
			$key = $context . chr(4) . $singular;
			$ret = $this->ngettext($key, $plural, $number);
			if ( strpos($ret, "\004") !== false ) {
				return $singular;
			}
			return $ret;
		}
		return false;
	}

	/**
	 * Load mo file with given local.
	 *
	 * @access protected
	 * @param string $locale
	 * @param string $path
	 * @return bool
	 */
	protected function load(?string $locale = null, string $path = '/') : bool
	{
		$file = Stringify::formatPath("{$path}/{$locale}.mo");
		if ( File::exists($file) ) {
			$this->mo = fopen($file, 'rb');
			return $this->canTranslate = true;
		}
		return false;
	}

	/**
	 * Init 32 bit integer from stream.
	 *
	 * @access protected
	 * @return void
	 */
	protected function initCount() : void
	{
		// Revision
		$this->readInt();

		// Init
		$this->count['total'] = $this->readInt();
		$this->count['original'] = $this->readInt();
		$this->count['translation'] = $this->readInt();
	}

	/**
	 * Read 32 bit integer from stream.
	 *
	 * @access protected
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
	protected function read(int $bytes) : string
	{
		$data = '';
		if ( $bytes ) {
			fseek($this->mo, $this->position);
			while ($bytes > 0) {
				$chunk = fread($this->mo, $bytes);
				$data .= $chunk;
				$bytes -= strlen($chunk);
			}
			$this->position = ftell($this->mo);
		}
		return (string)$data;
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
		// low endian
		if ( $this->byteOrder == 0 ) {
			return unpack("V{$count}", $this->read(4 * $count));
		}

		// big endian
		return unpack("N{$count}", $this->read(4 * $count));
	}

	/**
	 * Load the translations tables from the Mo file.
	 *
	 * @access protected
	 * @return void
	 */
	protected function loadTables() : void
	{
		$isArrayOriginal = TypeCheck::isArray($this->tableOriginals);
		$isArrayTranslation = TypeCheck::isArray($this->tableTranslations);

		if ( $isArrayOriginal && $isArrayTranslation ) {
			return;
		}

		if ( !$isArrayOriginal ) {
			$this->setPosition($this->count['original']);
			$this->tableOriginals = $this->readIntArray($this->count['total'] * 2);
		}

		if ( !$isArrayTranslation ) {
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
	protected function getOriginalString(int $num) : string
	{
		$length = $this->tableOriginals[$num * 2 + 1];
		$offset = $this->tableOriginals[$num * 2 + 2];
		if ( !$length ) {
			return '';
		}
		$this->setPosition($offset);
		return $this->read($length);
	}

	/**
	 * Return string from translations table.
	 *
	 * @access protected
	 * @param int $num
	 * @return string
	 */
	protected function getTranslationString(int $num) : string
	{
		$length = $this->tableTranslations[$num * 2 + 1];
		$offset = $this->tableTranslations[$num * 2 + 2];
		if ( !$length ) {
			return '';
		}
		$this->setPosition($offset);
		return $this->read($length);
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
	protected function findString(string $string, int $start = -1, int $end = -1) : mixed
	{
		if ( ($start == -1) || ($end == -1) ) {
			$start = 0;
			$end = $this->count['total'];
		}

		if ( abs($start - $end) <= 1 ) {
			$txt = $this->getOriginalString($start);
			if ( $string == $txt ) {
				return $start;
			}
			return -1;
		}

		if ( $start > $end ) {
			return $this->findString($string, $end, $start);
		}

		$half = (int)(($start + $end) / 2);
		$cmp = strcmp($string, $this->getOriginalString($half));
		if ( $cmp == 0 ) {
			return $half;
		}

		if ( $cmp < 0 ) {
			return $this->findString($string, $start, $half);
		}

		return $this->findString($string, $half, $end);
	}

	/**
	 * Sanitize plural form expression for use in PHP eval call.
	 *
	 * @access protected
	 * @param string $exp
	 * @return string
	 */
	protected function sanitizePluralExpression(string $exp) : string
	{
		$reg = '@[^a-zA-Z0-9_:;\(\)\?\|\&=!<>+*/\%-]@';
		$exp = Stringify::replaceRegex(regex: $reg, replace: '', subject: $exp) . ';';
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
					$res .= Stringify::repeat(')', times: $p) . ';';
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
	protected function extractPluralForms(string $header) : string
	{
		$pattern = "/(^|\n)plural-forms: ([^\n]*)\n/i";

		if ( Stringify::match($pattern, $header, $matches) ) {
			$exp = $matches[2];
		} else {
			$exp = "nplurals=2; plural=n == 1 ? 0 : 1;";
		}

		return $exp;
	}

	/**
	 * Get possible plural forms from Mo header.
	 *
	 * @access protected
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
	protected function selectString(int $n) : int
	{
		$string = $this->getPluralForms();
		$string = Stringify::replace('nplurals', "\$total", $string);
		$string = Stringify::replace("n", (int)$n, $string);
		$string = Stringify::replace('plural', "\$plural", $string);
		$total = 0;
		$plural = 0;
		eval ("{$string}");
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
	protected function setPosition(int $position) : void
	{
		fseek($this->mo, $position);
		$this->position = ftell($this->mo);
	}
}
