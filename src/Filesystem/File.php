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

class File
{
	/**
	 * Analyse file.
	 *
	 * @access public
	 * @param string $path
	 * @return array
	 */
	public static function analyse($path)
	{
		return [
			'parent'      => self::getParentDir($path),
			'name'        => self::getName($path),
			'filename'    => self::getFileName($path),
			'extension'   => self::getExtension($path),
			'accessed'    => self::getLastAccess($path),
			'changed'     => self::getLastChange($path),
			'size'        => self::getSize($path),
			'permissions' => self::getPermissions($path)
		];
	}

	/**
	 * Get parent dir.
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	public static function getParentDir($path)
	{
		return dirname(Stringify::formatPath($path));
	}

	/**
	 * Get file extension.
	 *
	 * @access public
	 * @param string $path
	 * @param bool $format
	 * @return string
	 */
	public static function getExtension($path, $format = true)
	{
		$ext = pathinfo(
			Stringify::formatPath($path),
			PATHINFO_EXTENSION
		);
		if ( $format ) {
			$ext = strtolower($ext);
		}
		return $ext;
	}

	/**
	 * Get file name.
	 *
	 * @access public
	 * @param string $path
	 * @return string
	 */
	public static function getName($path)
	{
		$path = self::getFileName($path);
		$ext = self::getExtension($path);
		return Stringify::replace(".{$ext}",'',$path);
	}

	/**
	 * Get file full name.
	 *
	 * @access public
	 * @param string $path
	 * @return string
	 */
	public static function getFileName($path)
	{
		return basename(Stringify::formatPath($path));
	}

	/**
	 * Get file last access.
	 *
	 * @access public
	 * @param string $path
	 * @return mixed
	 */
    public static function getLastAccess($path)
    {
    	$path = Stringify::formatPath($path);
        if ( self::exists($path) ) {
            if ( ($access = fileatime($path)) ) {
                return $access;
            }
        }
        return false;
    }

	/**
	 * Get file last change.
	 *
	 * @access public
	 * @param string $path
	 * @return mixed
	 */
    public static function getLastChange($path)
    {
    	$path = Stringify::formatPath($path);
        if ( self::exists($path) ) {
            if ( ($change = filemtime($path)) ) {
                return $change;
            }
        }
        return false;
    }

	/**
	 * Get file size value.
	 *
	 * @access public
	 * @param string $path
	 * @return int
	 */
	public static function getFileSize($path)
	{
        return (int)@filesize($path);
	}

	/**
	 * Get file size.
	 *
	 * @access public
	 * @param string $path
	 * @param int $decimals
	 * @return string
	 */
	public static function getSize($path, $decimals = 2)
	{
        $format = ['B','KB','MB','GB','TB'];
        $size = self::getFileSize($path);
        $factor = floor((strlen(strval($size)) - 1) / 3);
        return sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$format[$factor];
	}

	/**
	 * Get file permissions.
	 *
	 * @access public
	 * @param bool $convert
	 * @return mixed
	 */
	public static function getPermissions($path, $convert = false)
	{
		$permissions = substr(sprintf('%o',@fileperms($path)),-4);
		return ($convert) ? intval($permissions) : $permissions;
	}

	/**
	 * Add string to file.
	 *
	 * @access public
	 * @param string $input
	 * @return void
	 */
	public static function addString($path, $input = '')
	{
		$handle = fopen($path,'a');
		fwrite($handle,$input);
		fclose($handle);
	}

	/**
	 * Add break to file.
	 *
	 * @access public
	 * @param string $path
	 * @return void
	 */
	public static function addBreak($path)
	{
		$handle = fopen($path,'a');
		fwrite($handle,PHP_EOL);
		fclose($handle);
	}

	/**
	 * Remove file.
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function remove($path)
	{
		if ( self::exists($path) ) {
			if ( @unlink($path) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Copy file.
	 *
	 * @access public
	 * @param string $path
	 * @param string $to
	 * @return bool
	 */
    public static function copy($path, $to)
    {
    	$dir = dirname($to);
    	if ( self::exists($path) && self::isDir($dir) ) {
	        if ( copy($path,$to) ) {
	            return true;
	        }
    	}
        return false;
    }

	/**
	 * Move file.
	 *
	 * @access public
	 * @param string $path
	 * @param string $to
	 * @return bool
	 */
    public static function move($path, $to)
    {
    	$dist = dirname($to);
    	if ( self::exists($path) && self::isDir($dist) ) {
	        if ( rename($path,$to) ) {
	            return true;
	        }
    	}
        return false;
    }

	/**
	 * Check whether path is regular file.
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
    public static function isFile($path)
    {
		if ( self::exists($path) ) {
			return is_file($path);
		}
		return false;
    }

	/**
	 * Check file empty.
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function isEmpty($path)
	{
		if ( self::exists($path) ) {
			clearstatcache();
			return (self::getFileSize($path) == 0);
		}
		return false;
	}

	/**
	 * Check file readable.
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function isReadable($path)
	{
		return is_readable($path);
	}

	/**
	 * Check file writable.
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function isWritable($path)
	{
		return is_writable($path);
	}
	
    /**
     * Add directory.
	 *
	 * @access public
	 * @param string $path
	 * @param int $permissions
	 * @param bool $recursive
	 * @return bool
	 */
    public static function addDir($path, $permissions = 0755, $recursive = true)
    {
    	if ( !self::isFile($path) && !self::isDir($path) ) {
    		if ( @mkdir($path,$permissions,$recursive) ) {
            	return true;
        	}
    	}
        return false;
    }

    /**
     * Check directory.
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
    public static function isDir($path)
    {
    	if ( self::exists($path) && is_dir($path) ) {
    		return true;
    	}
        return false;
    }

    /**
     * Remove directory.
	 *
	 * @access public
	 * @param string $dir
	 * @return bool
	 */
    public static function removeDir($path)
    {
    	if ( self::isDir($path) ) {
    		if ( @rmdir($path) ) {
            	return true;
        	}
    	}
        return false;
    }

    /**
     * Clear directory from content.
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
    public static function clearDir($path)
    {
		$handler = false;
		if ( self::isDir($path) ) {
			$handler = opendir($path);
		}
		if ( !$handler ) {
			return false;
		}
	   	while( $file = readdir($handler) ) {
			if ( $file !== '.' && $file !== '..' ) {
			    if ( !self::isDir("{$path}/{$file}") ) {
			    	@unlink("{$path}/{$file}");
			    } else {
			    	$dir = "{$path}/{$file}";
				    foreach( scandir($dir) as $file ) {
				        if ( '.' === $file || '..' === $file ) {
				        	continue;
				        }
				        if ( self::isDir("{$dir}/{$file}") ) {
				        	self::recursiveRemove("{$dir}/{$file}");
				        } else {
				        	@unlink("{$dir}/{$file}");
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
	 * @access private
	 * @param string $path
	 * @return void
	 */
	private static function recursiveRemove($path)
	{
		if ( self::isDir($path) ) {
			$objects = scandir($path);
			foreach ($objects as $object) {
				if ( $object !== '.' && $object !== '..' ) {
					if ( filetype("{$path}/{$object}") == 'dir' ) {
						self::recursiveRemove("{$path}/{$object}");
					} else {
						@unlink("{$path}/{$object}");
					}
				}
			}
			reset($objects);
			@rmdir($path);
		}
	}

	/**
	 * Check file exists without stream.
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function exists($path)
	{
		if ( file_exists($path) ) {
			return true;
		}
		return false;
	}

	/**
	 * Read file without stream.
	 *
	 * @access public
	 * @param string $path
	 * @return mixed
	 */
	public static function r($path)
	{
		return @file_get_contents($path);
	}

	/**
	 * Write file.
	 *
	 * @access public
	 * @param string $path
	 * @param string $input
	 * @param string $append
	 * @return mixed
	 */
	public static function w($path, $input = '', $append = false)
	{
		$flag = 0;
		if ( $append ) {
			$flag = FILE_APPEND;
			$input .= PHP_EOL;
		}
		return @file_put_contents($path,$input,$flag);
	}

	/**
	 * Scan path.
	 *
	 * @access public
	 * @param string $path
	 * @return array
	 */
	public static function scanDir($path = '.')
	{
		return Arrayify::diff(
			scandir($path),['.', '..']
		);
	}

	/**
	 * Index path files.
	 *
	 * @access public
	 * @param string $path
	 * @return mixed
	 */
	public static function index($path)
	{
		$files = glob(Stringify::formatPath($path));
		return Arrayify::combine(
			$files,array_map('filectime',$files)
		);
	}

	/**
	 * Get last created file path.
	 *
	 * @access public
	 * @param string $path
	 * @return string
	 */
	public static function last($path)
	{
		$files = self::index($path);
		arsort($files);
		return (string)key($files);
	}

	/**
	 * Get first created file path.
	 *
	 * @access public
	 * @param string $path
	 * @return string
	 */
	public static function first($path)
	{
		$files = self::index($path);
		asort($files);
		return (string)key($files);
	}

	/**
	 * Get files count.
	 *
	 * @access public
	 * @param string $path
	 * @return mixed
	 */
	public static function count($path = '.')
	{
		$files = self::index($path);
		return (int)count($files);
	}

	/**
	 * Parse ini file.
	 *
	 * @access public
	 * @param string $path
	 * @param bool $sections
	 * @param int $mode
	 * @return mixed
	 */
	public static function parseIni($path, $sections = false, $mode = INI_SCANNER_NORMAL)
	{
		return parse_ini_file(
			Stringify::formatPath($path),$sections,$mode
		);
	}

	/**
	 * Import file from url.
	 * 
	 * @access public
	 * @param string $url
	 * @param string $path
	 * @return bool
	 */
	public static function import($url, $path)
	{
		$tmp = @fopen($path,'w');
		$status = fwrite($tmp,self::r($url));
		fclose($tmp);
		return (bool)$status;
	}

	/**
	 * Download file.
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function download($path)
	{
		if ( self::exists($path) ) {
			$file = self::r($path);
			$filename = Stringify::replace(' ','-',basename($path));
			header('Content-type: application/force-download');
			header("Content-Disposition: attachment; filename={$filename};");
			echo $file;
			die();
		}
		return false;
	}
}
