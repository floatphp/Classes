<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.0.2
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2023 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

final class Upload
{
	/**
	 * @access public
	 * @param string $item
	 * @return mixed
	 */
	public static function get($item = null)
	{
		if ( $item ) {
			return self::isSetted($item) ? $_FILES[$item] : null;
		}
		return self::isSetted() ? $_FILES : null;
	}

	/**
	 * @access public
	 * @param string $item
	 * @param mixed $value
	 * @return void
	 */
	public static function set($item, $value = null)
	{
		$_FILES[$item] = $value;
	}
	
	/**
	 * @access public
	 * @param string $item
	 * @return bool
	 */
	public static function isSetted($item = null)
	{
		if ( $item ) {
			return isset($_FILES[$item]);
		}
		return isset($_FILES) && !empty($_FILES);
	}

	/**
	 * @access public
	 * @param string $upload
	 * @param string $file
	 * @return mixed
	 */
	public static function do($upload, $file = null)
	{
		if ( self::isSetted() ) {
			if ( !$_FILES['file']['error'] ) {
				$tmp = ($file) ? $file : $_FILES['file']['tmp_name'];
				$name = ($file) ? basename($file) : $_FILES['file']['name'];
				self::moveUploadedFile($tmp,"{$upload}/{$name}");
				return "{$upload}/{$name}";
			}
		}
		return false;
	}

	/**
	 * Move uploaded file.
	 * 
	 * @access public
	 * @param string $tmp
	 * @param string $file
	 * @return bool
	 * @todo getAllowedMimes
	 */
	public static function moveUploadedFile($tmp, $file)
	{
		return move_uploaded_file($tmp, $file);
	}
}
