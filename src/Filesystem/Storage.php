<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.0.0
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Filesystem;

use SleekDB\Store;

/**
 * Wrapper class for external storage database
 */
class Storage
{
	/**
	 * @access protected
	 * @var object $adapter
	 */
	protected $adapter;

	/**
	 * @param string $table
	 * @param string $dir
	 * @param array $config
	 */
	public function __construct($table = 'table', $dir = 'database', $config = [])
	{
		if ( !File::isDir($dir) ) {
			File::addDir($dir);
		}
		$this->adapter = new Store($table,$dir,$config);
	}

	/**
	 * @access public
	 * @param void
	 * @return object
	 */
	public function getAdapter() : object
	{
		return $this->adapter;
	}
}
