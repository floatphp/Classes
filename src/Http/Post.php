<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes HTTP Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 */

namespace floatPHP\Classes\Http;

class Post
{
	/**
	 * @param string|null $item
	 * @return array|string
	 */
	public static function get($item = null)
	{
		if (isset($item)) return $_POST[$item];
		else return $_POST;
	}

	/**
	 * @param string|null $item
	 * @return boolean|null
	 */
	public static function isSetted($item = null)
	{
		if (isset($_POST[$item])) return true;
	}
}
