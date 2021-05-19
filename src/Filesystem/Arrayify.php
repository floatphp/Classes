<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Filesystem Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Filesystem;

final class Arrayify
{
	/**
	 * @access public
	 * @param array $arrays
	 * @return array
	 */
	public static function merge(array ...$arrays) : array
	{
		return array_merge(...$arrays);
	}
}
