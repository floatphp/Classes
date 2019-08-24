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

class Api
{
	public static function connect($url)
	{
		$request = new Curl($url);
		return $request->execute();
	}
	
	public static function convertCSV($csv)
	{
		$array = array_map("str_getcsv", explode("\n", $csv));
		// $json  = json_encode($array);
		return $array;
	}
}
