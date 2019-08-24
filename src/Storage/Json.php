<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Storage Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 */

namespace floatphp\Classes\Storage; 

class Json extends File
{
	/**
	* @ OK
	*/
	private $vars;
	/**
	* @ OK
	*/
	const EXT = '.json';
	
	/**
	* @ OK
	*/
	public function __construct($path = NULL,$mode = 'r')
	{
		parent::__construct($path. self::EXT ,$mode);
		$this->read();
	}
	/**
	* @ OK
	*/
	public function parse()
	{
     	return $this->vars = json_decode($this->content, true);
	}
	/**
	* @ OK
	*/
	public function parseObject()
	{
     	return $this->vars = json_decode($this->content, false);
	}
}
