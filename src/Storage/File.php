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

use \App\System\Exceptions\Classes\FileException;
use \App\System\Interfaces\Classes\FileInterface;
use \Exception;

class File implements FileInterface
{
	/**
	 * @access public
	 */
	public $path;
	public $root;
	public $name;
	public $ext;
	public $parent;
	public $content;

	/**
	 * @access private
	 */
	private $handler;

	/**
	 * @access protected
	 */
	protected $mode;

	/**
	 * File object
	 *
	 * @param string $path, $mode
	 * @return void
	 */
	public function __construct($path = null, $mode = 'r')
	{
		$this->set($path,$mode);
		$this->isReady();
		$this->open();
	}

	/**
	 * define properties
	 *
	 * @param string $path, $mode
	 * @return void
	 */
	private function set($path,$mode)
	{
		$this->path   = $path;
		$this->mode   = $mode;
		$this->parent = dirname($this->path);
		$this->root   = realpath($this->path);
		$this->ext    = pathinfo($this->path, PATHINFO_EXTENSION);
		$this->name   = str_replace('.'.$this->ext,'',$this->path);
	}

	/**
	 * check file
	 *
	 * @param void
	 * @return boolean
	 */
	public function isReady()
	{
		try
		{
			if (!$this->exists()) throw new FileException('notfound');
			elseif (!$this->readable()) throw new FileException('unreadable');
			else return true;
		}
		catch (FileException $e)
		{
			die($e->message());
		}
	}

	/**
	 * create file
	 *
	 * @param void
	 * @return void
	 */
	protected function create()
	{
		$this->handler = fopen($this->path, 'w');
	}

	/**
	 * open file
	 *
	 * @param void
	 * @return void
	 */
	protected function open()
	{
		if ($this->exists($this->path))
		{
			$this->handler = fopen($this->path, $this->mode);
		}
	}

	/**
	 * write file
	 *
	 * @param string $input
	 * @return void
	 */
	public function write($input)
	{
		fwrite(fopen($this->path, 'w'), $input);
	}

	/**
	 * add string to file
	 *
	 * @param string $input
	 * @return void
	 */
	public function addString($input)
	{
		fwrite(fopen($this->path, 'a'), $input);
	}

	/**
	 * add space to file
	 *
	 * @param void
	 * @return void
	 */
	public function addSpace()
	{
		fwrite(fopen($this->path, 'a'), PHP_EOL);
	}

	/**
	 * read file
	 *
	 * @param void
	 * @return void
	 */
	public function read()
	{
		if ($this->exists($this->path) && !$this->isEmpty()) 
		{
			$this->content = fread($this->handler,filesize($this->path));
		}
	}

	/**
	 * close file handler
	 *
	 * @param void
	 * @return void
	 */
	public function close()
	{
		fclose($this->handler);
	}

	/**
	 * delete file object
	 *
	 * @param void
	 * @return boolean
	 */
	public function delete()
	{
		$this->close();
		unlink($this->path);
		if ( !$this->exists() ) return true;
	}

	/**
	 * check file exists
	 *
	 * @param void
	 * @return boolean
	 */
	protected function exists()
	{
		if (is_file($this->path) && file_exists($this->path))
		return true;
	}

	/**
	 * check file readable
	 *
	 * @param void
	 * @return boolean
	 */
	protected function readable()
	{
		if (!fopen($this->path, 'r') === false)
		return true;
	}

	/**
	 * check file empty
	 *
	 * @param void
	 * @return boolean
	 */
	public function isEmpty()
	{
		if ($this->exists($this->path))
		{
			if (filesize($this->path) == 0) return true;
		}
	}

}
