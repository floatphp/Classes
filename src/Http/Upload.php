<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Http Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Http;

final class Upload
{
	/**
	 * @access public
	 * @param string $item null
	 * @return mixed
	 */
	public static function get($item = null)
	{
		if ( $item ) {
			return self::isSetted($item) ? $_FILES[$item] : false;
		} else {
			return $_FILES;
		}
	}

	/**
	 * @access public
	 * @param string $item
	 * @param mixed $value
	 * @return void
	 */
	public static function set($item, $value)
	{
		$_FILES[$item] = $value;
	}
	
	/**
	 * @access public
	 * @param string $item null
	 * @return bool
	 */
	public static function isSetted($item = null)
	{
		if ( $item ) {
			return isset($_FILES[$item]);
		} else {
			return isset($_FILES);
		}
	}

	/**
	 * @access public
	 * @param string $upload
	 * @param string $file null
	 * @return mixed
	 */
	public static function doUpload($upload, $file = null)
	{
		if ( isset($_FILES) && !$_FILES['file']['error'] ) {
			$tmp = ($file) ? $file : $_FILES['file']['tmp_name'];
			$name = ($file) ? basename($file) : $_FILES['file']['name'];
			move_uploaded_file($tmp, "{$upload}/{$name}");
			return "{$upload}/{$name}";
		} else return false;
	}
}
