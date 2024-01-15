<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.1.0
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Filesystem\{
	File, Arrayify, Stringify, TypeCheck
};

final class Upload
{
    /**
     * @access public
     * @var array $handler
     */
    public static $handler = [];

	/**
	 * Get _FILES value.
	 * 
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public static function get(?string $key = null)
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
	public static function set(string $key, $value = null)
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
	public static function isSetted(?string $key = null)
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
    public static function unset(?string $key = null)
    {
        if ( $key ) {
            unset($_FILES[$key]);

        } else {
            $_FILES = [];
        }
    }

	/**
	 * Move uploaded file.
	 * 
	 * @access public
	 * @param string $temp
	 * @param string $path
	 * @return bool
	 */
	public static function moveUploadedFile(string $temp, string $path) : bool
	{
		return move_uploaded_file($temp, $path);
	}

	/**
	 * Upload file (Secured).
	 * 
	 * @access public
	 * @param string $upload Path
	 * @param array $args File
	 * @param array $types Mime
	 * @return mixed
	 */
	public static function do(string $upload, array $args = [], array $types = [])
	{
		if ( self::isSetted('file') ) {

			// Handle
			static::$handler = Arrayify::merge([
				'error'    => false,
				'name'     => false,
				'tmp_name' => false,
				'type'     => false,
				'size'     => 0
			], self::get('file'));

			// Format
			static::$handler['error'] = (bool)static::$handler['error'];
			static::$handler['temp']  = static::$handler['tmp_name'];
			unset(static::$handler['tmp_name']);

			// Check error
			if ( static::$handler['error'] ) return false;

			// Check type
			if ( empty($types) ) {
				$types = File::mimes();
			}
			if ( !Arrayify::inArray(static::$handler['type'], $types)) {
				return false;
			}

			// Check size
			if ( isset($args['size']) ) {
				$min = $args['size']['min'] ?? false;
				$max = $args['size']['max'] ?? false;
				if ( TypeCheck::isInt($min) && (static::$handler['size'] < $min) ) {
					return false;
				}
				if ( TypeCheck::isInt($max) && (static::$handler['size'] > $max) ) {
					return false;
				}
			}

			// Unique
			if ( isset($args['unique']) && $args['unique'] == true ) {
				$ext  = File::getExtension(static::$handler['name']);
				$date = date('dmyhis');
				$name = Stringify::remove(".{$ext}", static::$handler['name']);
				static::$handler['name'] = "{$name}-{$date}.{$ext}";
			}

			// Upload
			$name = static::$handler['name'];
			$temp = static::$handler['temp'];
			if ( self::moveUploadedFile($temp, "{$upload}/{$name}") ) {
				return $name;
			}
		}

		return false;
	}
}
