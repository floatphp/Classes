<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Filesystem Component
 * @version   : 1.1.0
 * @category  : PHP framework
 * @copyright : (c) JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

declare(strict_types = 1);

namespace floatPHP\Classes\Filesystem;

use floatphp\Interfaces\Classes\FileInterface;

class File // implements FileInterface
{
	/**
	 * @access protected
	 * @var string $path
	 * @var string $name
	 * @var string $size
	 * @var string $extension
	 * @var string $content
	 * @var string $parentDir
	 */
	protected $path = null;
	protected $name = null;
	protected $size = null;
	protected $extension = null;
	protected $content = null;
	protected $parentDir = null;

	/**
	 * @access private
	 * @var stream $handler
	 */
	private $handler = null;

	/**
	 * Construct file object
	 *
	 * @param string $path null
	 */
	public function __construct($path = null)
	{
		if ( $path && !$this->exists($this->path = $path) ) {
			$this->write();
		}
		$this->analyze();
	}

	/**
	 * Analyze File
	 *
	 * @access protected
	 * @param void
	 * @return void
	 */
	protected function analyze()
	{
		if ( $this->path && ($this->path = realpath($this->path)) ) {
			$this->parentDir = dirname($this->path);
			$this->extension = pathinfo($this->path, PATHINFO_EXTENSION);
			$this->name = basename(str_replace(".{$this->extension}", '', $this->path));
			$this->extension = strtolower($this->extension);
			$this->size = filesize($this->path);
		}
	}

	/**
	 * Open File
	 *
	 * @access protected
	 * @param string $mode 'c+'
	 * @return void
	 */
	protected function open($mode = 'c+')
	{
		clearstatcache();
		if ( $this->exists($this->path) ) {
			$this->handler = fopen($this->path, $mode);
		}
	}

	/**
	 * Close file handler
	 *
	 * @access protected
	 * @param void
	 * @return void
	 */
	protected function close()
	{
		if ( $this->handler ) {
			fclose($this->handler);
			$this->handler = null;
		}
		clearstatcache();
	}

	/**
	 * @access protected
	 * @param string $dir
	 * @return void
	 */
	protected function recursiveRemove($dir)
	{
		if ( is_dir($dir) ) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object !== '.' && $object !== '..') {
					if (filetype("{$dir}/{$object}") == 'dir') {
						$this->recursiveRemove("{$dir}/{$object}");
					}
					else unlink("{$dir}/{$object}");
				}
			 }
			reset($objects);
			rmdir($dir);
		}
	}

	/**
	 * Get Parent Dir
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	public function getParentDir()
	{
		return $this->parentDir;
	}

	/**
	 * Get file content
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Get file last access
	 *
	 * @access public
	 * @param void
	 * @return mixed
	 */
    public function getLastAccess()
    {
        if ( $this->exists($this->path) ) {
            if ( ($access = fileatime($this->path)) ) {
                return $access;
            } else {
                return false;
            }
        }
    }

	/**
	 * Get file last change
	 *
	 * @access public
	 * @param void
	 * @return mixed
	 */
    public function getLastChange()
    {
        if ( $this->exists($this->path) ) {
            if ( ($change = filemtime($this->path)) ) {
                return $change;
            } else {
                return false;
            }
        }
    }

	/**
	 * Get File Size int
	 *
	 * @access public
	 * @param void
	 * @return int
	 */
	public function getFileSize()
	{
        return $this->size;
	}

	/**
	 * Get File Size
	 *
	 * @access public
	 * @param int $decimals 2
	 * @return string
	 */
	public function getSize($decimals = 2)
	{
        $size = ['B','KB','MB','GB','TB'];
        $factor = floor((strlen(strval($this->size)) - 1) / 3);
        return sprintf("%.{$decimals}f", $this->size / pow(1024, $factor)) . @$size[$factor];
	}

	/**
	 * Read File & Get Content
	 *
	 * @access public
	 * @param boolean $return false
	 * @return mixed
	 */
	public function read($return = false)
	{
		$this->open();
		if ( $this->handler && $this->isReadable() ) {
			if ( $this->isEmpty() ) {
				$this->content = '';
			} else {
				$this->content = fread($this->handler, filesize($this->path));
			}
			if ($return) {
				$this->close();
				return $this->content;
			}
		}
		$this->close();
	}

	/**
	 * Write File & create folder if not exists
	 *
	 * @access public
	 * @param string $input
	 * @return void
	 */
	public function write($input = '')
	{
		$dir = dirname($this->path);
		if ( !is_file($dir) && !is_dir($dir) ) {
			if ( !$this->addDir($dir) ) {
				return false;
			}
		}
		if ( ($this->handler = fopen($this->path, 'w', true)) ) {
			fwrite($this->handler, $input);
		}
		$this->close();
	}

	/**
	 * Add String to File
	 *
	 * @access public
	 * @param string $input
	 * @return void
	 */
	public function addString($input)
	{
		$this->open('a');
		if ( $this->handler ) {
			fwrite($this->handler, $input);
		}
		$this->close();
	}

	/**
	 * Add space to file
	 *
	 * @access public
	 * @param void
	 * @return void
	 */
	public function addBreak()
	{
		fwrite(fopen($this->path, 'a'), PHP_EOL);
	}

	/**
	 * Delete file object
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	public function delete()
	{
		$this->close();
		if ( $this->exists() ) {
			if ( unlink($this->path) ) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}

	/**
	 * Copy file
	 *
	 * @access public
	 * @param void
	 * @return string $dest
	 */
    public function copy($dest)
    {
    	$this->close();
        if ( copy($this->path, $dest) ) {
            return true;
        }
        return false;
    }

	/**
	 * Move file
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
    public function move($dest)
    {
    	$this->close();
        if ( rename($this->path, $dest) ) {
            return true;
        }
        return false;
    }

	/**
	 * Check whether path is regular file
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
    public function isFile()
    {
        if ( $this->path && is_file($this->path) ) {
            return true;
        }
        return false;
    }

    /**
     * Add directory
	 *
	 * @access public
	 * @param string $dir
	 * @param int $mode
	 * @param boolean $recursive
	 * @return boolean
	 */
    public function addDir($dir, $mode = 0755, $recursive = true)
    {
    	if ( !is_file($dir) && !is_dir($dir) ) {
    		if ( @mkdir($dir, $mode, $recursive) ) {
            	return true;
        	}
    	}
        return false;
    }

    /**
     * Remove directory
	 *
	 * @access public
	 * @param string $dir
	 * @return boolean
	 */
    public function removeDir($dir)
    {
    	if ( !is_file($dir) && is_dir($dir) ) {
    		if ( @rmdir($dir) ) {
            	return true;
        	}
    	}
        return false;
    }

    /**
     * Remove directory content
	 *
	 * @access public
	 * @param string $dir
	 * @return boolean
	 */
    public function emptyDir($dir)
    {
		$handler = false;
		if ( is_dir($dir) ) {
			$handler = opendir($dir);
		}
		if ( !$handler ) {
			return false;
		}
	   	while( $file = readdir($handler) ) {
			if ($file !== '.' && $file !== '..') {
			    if ( !is_dir($dir.'/'.$file) ) {
			    	@unlink($dir.'/'.$file);
			    } else {
			    	$dir = $dir.'/'.$file;
				    foreach( scandir($dir) as $file ) {
				        if ( '.' === $file || '..' === $file ) {
				        	continue;
				        }
				        if ( is_dir("{$dir}/{$file}") ) {
				        	$this->recursiveRemove("{$dir}/{$file}");
				        } else {
				        	unlink("{$dir}/{$file}");
				        }
				    }
				    @rmdir($dir);
			    }
			}
	   }
	   closedir($handler);
	   return true;
    }

	/**
	 * Check File Exists
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	public function exists()
	{
		if ( $this->isFile() && file_exists($this->path) ) {
			return true;
		}
		return false;
	}

	/**
	 * Check file readable
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	public function isReadable()
	{
		if ( $this->path && !fopen($this->path, 'r') === false ) {
			return true;
		}
		return false;
	}

	/**
	 * Check file writable
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	public function isWritable()
	{
		return is_writable($this->path) ? true : false;
	}

	/**
	 * Check file empty
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	public function isEmpty()
	{
		if ( $this->exists($this->path) && filesize($this->path) == 0 ) {
			return true;
		}
		return false;
	}

	/**
	 * Read file without stream
	 *
	 * @access public
	 * @param string $path
	 * @return mixed
	 */
	public static function r($path)
	{
		return file_get_contents($path);
	}

	/**
	 * Write file without stream
	 *
	 * @access public
	 * @param string $path
	 * @param string $input empty
	 * @param string $append false
	 * @return mixed
	 */
	public static function w($path, $input = '', $append = false)
	{
		$flag = 0;
		if ($append) {
			$flag = FILE_APPEND;
		}
		return file_put_contents($path, $input, $flag);
	}
}
