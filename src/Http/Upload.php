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
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Filesystem\{
	File,
	Arrayify,
	Stringify,
	TypeCheck,
	Validator
};
use FloatPHP\Classes\Security\Tokenizer;

/**
 * Advanced upload manipulation.
 */
final class Upload
{
	/**
	 * Get _FILES value.
	 *
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public static function get(?string $key = null) : mixed
	{
		if ( $key ) {
			return self::isSetted($key) ? $_FILES[$key] : null;
		}
		return self::isSetted() ? $_FILES : null;
	}

	/**
	 * Set _FILES value.
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public static function set(string $key, $value = null) : void
	{
		$_FILES[$key] = $value;
	}

	/**
	 * Check _FILES value.
	 *
	 * @access public
	 * @param string $key
	 * @return bool
	 */
	public static function isSetted(?string $key = null) : bool
	{
		if ( $key ) {
			return isset($_FILES[$key]);
		}
		return isset($_FILES) && !empty($_FILES);
	}

	/**
	 * Unset _FILES value.
	 *
	 * @access public
	 * @param string $key
	 * @return void
	 */
	public static function unset(?string $key = null) : void
	{
		if ( $key ) {
			unset($_FILES[$key]);

		} else {
			$_FILES = [];
		}
	}

	/**
	 * Move uploaded files.
	 *
	 * @access public
	 * @param string $from
	 * @param string $to
	 * @return bool
	 */
	public static function move(string $from, string $to) : bool
	{
		$to = Stringify::formatPath($to);
		return move_uploaded_file($from, $to);
	}

	/**
	 * Handle uploaded file.
	 *
	 * @access public
	 * @param string $upload Path
	 * @param array $args File
	 * @param array $types Mime
	 * @return mixed
	 * @todo
	 */
	public static function handle(string $upload, array $args = [], array $types = [])
	{
		// if ( self::isSetted('file') ) {

		// 	// Handle
		// 	static::$handler = Arrayify::merge([
		// 		'error'    => false,
		// 		'name'     => false,
		// 		'tmp_name' => false,
		// 		'type'     => false,
		// 		'size'     => 0
		// 	], self::get('file'));

		// 	// Format
		// 	static::$handler['error'] = (bool)static::$handler['error'];
		// 	static::$handler['temp']  = static::$handler['tmp_name'];
		// 	unset(static::$handler['tmp_name']);

		// 	// Check error
		// 	if ( static::$handler['error'] ) return false;

		// 	// Check type
		// 	if ( empty($types) ) {
		// 		$types = File::mimes();
		// 	}
		// 	if ( !Arrayify::inArray(static::$handler['type'], $types)) {
		// 		return false;
		// 	}

		// 	// Check size
		// 	if ( isset($args['size']) ) {
		// 		$min = $args['size']['min'] ?? false;
		// 		$max = $args['size']['max'] ?? false;
		// 		if ( TypeCheck::isInt($min) && (static::$handler['size'] < $min) ) {
		// 			return false;
		// 		}
		// 		if ( TypeCheck::isInt($max) && (static::$handler['size'] > $max) ) {
		// 			return false;
		// 		}
		// 	}

		// 	// Unique
		// 	if ( isset($args['unique']) && $args['unique'] == true ) {
		// 		$ext  = File::getExtension(static::$handler['name']);
		// 		$date = date('dmyhis');
		// 		$name = Stringify::remove(".{$ext}", static::$handler['name']);
		// 		static::$handler['name'] = "{$name}-{$date}.{$ext}";
		// 	}

		// 	// Upload
		// 	$name = static::$handler['name'];
		// 	$temp = static::$handler['temp'];
		// 	if ( self::move($temp, "{$upload}/{$name}") ) {
		// 		return $name;
		// 	}
		// }

		// return false;
	}

	/**
	 * Sanitize uploaded files.
	 *
	 * @access public
	 * @param array $files, $_FILES
	 * @param array $types, Mime types
	 * @return array
	 */
	public static function sanitize(array $files, ?array $types = []) : array
	{
		$data = [];
		$types = self::getMimes($types);

		foreach ($files as $file) {

			if ( $file['error'] ) {
				continue;
			}

			$name = $file['name'];
			if ( !($ext = File::getExtension($name)) ) {
				continue;
			}

			$temp = $file['tmp_name'];
			$type = File::getMimeType($temp, $ext, $types);
			if ( $type == 'undefined' ) {
				continue;
			}

			if ( !Validator::isMime($name, $types) ) {
				continue;
			}

			$rand = Tokenizer::getUniqueId(false);
			$name = Stringify::remove(".{$ext}", $name);
			$name = Stringify::slugify("{$name}");
			$path = "{$name}.{$ext}";
			$path = (substr($path, 0, 1) !== '.') ? "{$rand}-{$path}" : "{$rand}{$path}";

			$key = (!empty($name)) ? $name : $ext;
			$data[$key] = [
				'path' => $path,
				'temp' => $temp
			];
		}

		return $data;
	}

	/**
	 * Get upload allowed mime types.
	 *
	 * @access public
	 * @param array $types
	 * @return array
	 */
	public static function getMimes(?array $types = []) : array
	{
		$mimes = [
			'txt'  => 'text/plain',
			'csv'  => 'text/csv',
			'tsv'  => 'text/tab-separated-values',
			'ics'  => 'text/calendar',
			'rtx'  => 'text/richtext',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif'  => 'image/gif',
			'png'  => 'image/png',
			'bmp'  => 'image/bmp',
			'mp3'  => 'audio/mpeg',
			'ogg'  => 'audio/ogg',
			'wav'  => 'audio/wav',
			'mp4'  => 'video/mp4',
			'mpeg' => 'video/mpeg',
			'ogv'  => 'video/ogg',
			'zip'  => 'application/zip',
			'rar'  => 'application/rar',
			'7z'   => 'application/x-7z-compressed',
			'pdf'  => 'application/pdf',
			'doc'  => 'application/msword',
			'xls'  => 'application/vnd.ms-excel',
			'xla'  => 'application/vnd.ms-excel',
			'ppt'  => 'application/vnd.ms-powerpoint',
			'mdb'  => 'application/vnd.ms-access',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		];
		return Arrayify::merge($types, $mimes);
	}
}
