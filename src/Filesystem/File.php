<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.1.1
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

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
	public static function analyse($path) : array
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
	 * @param string $path
	 * @return string
	 */
	public static function getParentDir(string $path) : string
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
	public static function getExtension(string $path, bool $format = true) : string
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
	 * Get file name without extension.
	 *
	 * @access public
	 * @param string $path
	 * @return string
	 */
	public static function getName(string $path) : string
	{
		$path = Stringify::basename($path);
		return Stringify::replaceRegex('/\.[^.]+$/', '', $path);
	}

	/**
	 * Get file full name.
	 *
	 * @access public
	 * @param string $path
	 * @return string
	 */
	public static function getFileName(string $path) : string
	{
		return Stringify::basename($path);
	}

	/**
	 * Get file last access.
	 *
	 * @access public
	 * @param string $path
	 * @return mixed
	 */
    public static function getLastAccess(string $path)
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
    public static function getLastChange(string $path)
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
	public static function getFileSize(string $path) : int
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
	public static function getSize($path, int $decimals = 2) : string
	{
        $format = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = self::getFileSize($path);
        $factor = floor((strlen(strval($size)) - 1) / 3);
        return sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$format[$factor];
	}

	/**
	 * Get file permissions.
	 *
	 * @access public
	 * @param string $path
	 * @param bool $convert
	 * @return mixed
	 */
	public static function getPermissions(string $path, bool $convert = false)
	{
		$permissions = substr(sprintf('%o', @fileperms($path)), -4);
		return ($convert) ? intval($permissions) : $permissions;
	}

	/**
	 * Get file lines.
	 * 
	 * [Ignore new : 2]
	 * [Skip empty : 4]
	 *
	 * @access public
	 * @param string $path
	 * @param array $exclude
	 * @param int $flags
	 * @return array
	 */
	public static function getLines(string $path, array $exclude = [], int $flags = 2|4) : array
	{
		$lines = [];
		if ( ($lines = @file($path, $flags)) ) {
			if ( $exclude ) {
				foreach ($lines as $key => $value) {
					foreach ($exclude as $search) {
						if ( Stringify::contains($value, $search) ) {
							unset($lines[$key]);
						}
					}
				}
			}
		}
		return $lines;
	}

	/**
	 * Add string to file.
	 *
	 * @access public
	 * @param string $path
	 * @param string $input
	 * @return void
	 */
	public static function addString(string $path, string $input = '')
	{
		$handle = @fopen($path, 'a');
		@fwrite($handle, (string)$input);
		fclose($handle);
	}

	/**
	 * Add break to file.
	 *
	 * @access public
	 * @param string $path
	 * @return void
	 */
	public static function addBreak(string $path)
	{
		$handle = @fopen($path, 'a');
		@fwrite($handle, PHP_EOL);
		fclose($handle);
	}

	/**
	 * Remove file.
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function remove(string $path) : bool
	{
		if ( self::isFile($path) ) {
			return @unlink($path);
		}
		return false;
	}

	/**
	 * Copy file.
	 *
	 * @access public
	 * @param string $path
	 * @param string $to
	 * @param resource $context
	 * @return bool
	 */
    public static function copy(string $path, string $to, $context = null) : bool
    {
    	$dir = dirname($to);
    	if ( self::exists($path) && self::isDir($dir) ) {
	        return copy($path, $to, $context);
    	}
        return false;
    }

	/**
	 * Move file.
	 *
	 * @access public
	 * @param string $path
	 * @param string $to
	 * @param resource $context
	 * @return bool
	 */
    public static function move(string $path, string $to, $context = null) : bool
    {
    	$dir = dirname($to);
    	if ( self::exists($path) && self::isDir($dir) ) {
	        return rename($path, $to, $context);
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
    public static function isFile(string $path) : bool
    {
		if ( self::exists($path) ) {
			return @is_file($path);
		}
		return false;
    }

	/**
	 * Check file empty.
	 *
	 * @access public
	 * @param string $path
	 * @return mixed
	 */
	public static function isEmpty(string $path)
	{
		if ( self::exists($path) ) {
			return (self::getFileSize($path) == 0);
		}
		return null;
	}

	/**
	 * Check file readable.
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function isReadable(string $path) : bool
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
	public static function isWritable(string $path) : bool
	{
		return is_writable($path);
	}
	
    /**
     * Add directory.
	 *
	 * @access public
	 * @param string $path
	 * @param int $p permissions
	 * @param bool $r recursive
	 * @param resource $c context
	 * @return bool
	 */
    public static function addDir(string $path, int $p = 0755, bool $r = true, $c = null) : bool
    {
    	if ( !self::isFile($path) && !self::isDir($path) ) {
    		if ( TypeCheck::isResource($c) ) {
    			return @mkdir($path, $p, $r, $c);
    		}
    		return @mkdir($path, $p, $r);
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
    public static function isDir(string $path) : bool
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
	 * @param string $path
	 * @param bool $clear, Recursively
	 * @return bool
	 */
    public static function removeDir(string $path, bool $clear = false) : bool
    {
    	if ( self::isDir($path) ) {
			if ( $clear ) self::clearDir($path);
    		return @rmdir($path);
    	}
        return false;
    }

    /**
     * Clear directory recursively.
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
    public static function clearDir(string $path) : bool
    {
		$handler = false;

		if ( self::isDir($path) ) {
			$handler = @opendir($path);
		}

		if ( !TypeCheck::isResource($handler) ) {
			return false;
		}

	   	while( $file = readdir($handler) ) {
			if ( $file !== '.' && $file !== '..' ) {
			    if ( !self::isDir("{$path}/{$file}") ) {
			    	self::remove("{$path}/{$file}");

			    } else {
			    	$dir = "{$path}/{$file}";
				    foreach( @scandir($dir) as $file ) {
				        if ( '.' === $file || '..' === $file ) {
				        	continue;
				        }
				        if ( self::isDir("{$dir}/{$file}") ) {
				        	self::recursiveRemove("{$dir}/{$file}");

				        } else {
				        	self::remove("{$dir}/{$file}");
				        }
				    }
				    self::removeDir($dir);
			    }
			}
	   }
	   closedir($handler);
	   return true;
    }

	/**
	 * Directory recursive remove.
	 * 
	 * @access private
	 * @param string $path
	 * @return void
	 */
	private static function recursiveRemove(string $path)
	{
		if ( self::isDir($path) ) {
			$items = @scandir($path);
			foreach ($items as $item) {
				if ( $item !== '.' && $item !== '..' ) {
					if ( self::isDir("{$path}/{$item}") ) {
						self::recursiveRemove("{$path}/{$item}");
					} else {
						self::remove("{$path}/{$item}");
					}
				}
			}
			reset($items);
			self::removeDir($path);
		}
	}

	/**
	 * Check path exists (file|directory).
	 *
	 * @access public
	 * @param string $path
	 * @return bool
	 */
	public static function exists(string $path) : bool
	{
		clearstatcache();
		return file_exists($path);
	}

	/**
	 * Read file.
	 *
	 * @access public
	 * @param string $path
	 * @param bool $inc (Use include path)
	 * @param resource|array $context
	 * @param int $offset
	 * @return mixed
	 */
	public static function r(string $path, bool $inc = false, $context = null, int $offset = 0)
	{
		if ( TypeCheck::isStream($path) ) {
			if ( TypeCheck::isArray($context) ) {
				$context = stream_context_create($context);
			}
		}
		return @file_get_contents($path, $inc, $context, $offset);
	}

	/**
	 * Write file.
	 *
	 * @access public
	 * @param string $path
	 * @param mixed $input
	 * @param bool $append
	 * @return bool
	 */
	public static function w(string $path, $input = '', bool $append = false) : bool
	{
		$flag = 0;
		if ( $append ) {
			$flag = FILE_APPEND;
			$input .= PHP_EOL;
		}
		return (bool)@file_put_contents($path, $input, $flag);
	}

	/**
	 * Scan path (Directory).
	 * 
	 * [ASC : 0]
	 * [DESC : 1]
	 * [NO : 2]
	 *
	 * @access public
	 * @param string $path
	 * @param int $sort
	 * @param array $except
	 * @return array
	 */
	public static function scanDir(string $path = '.', int $sort = 0, array $except = []) : array
	{
		$except = Arrayify::merge(['.', '..'], $except);
		return Arrayify::diff(@scandir($path, $sort), $except);
	}

	/**
	 * Index path files using pattern.
	 *
	 * @access public
	 * @param string $path
	 * @return mixed
	 */
	public static function index(string $path = '.')
	{
		if ( Stringify::contains($path, '*')) {
			$dir = dirname($path);

		} else {
			$dir = $path;
			$path = Stringify::formatPath("{$path}/*.*");
		}
		if ( self::isDir($dir) ) {
			$files = glob($path);
			return Arrayify::combine(
				$files, Arrayify::map('filectime', $files)
			);
		}
		return false;
	}

	/**
	 * Get last created file path,
	 * Accept pattern.
	 *
	 * @access public
	 * @param string $path
	 * @return mixed
	 */
	public static function last(string $path = '.')
	{
		if ( ($files = self::index($path)) ) {
			arsort($files);
			return (string)key($files);
		}
		return false;
	}

	/**
	 * Get first created file path,
	 * Accept pattern.
	 *
	 * @access public
	 * @param string $path
	 * @return mixed
	 */
	public static function first(string $path = '.')
	{
		if ( ($files = self::index($path)) ) {
			asort($files);
			return (string)key($files);
		}
		return false;
	}

	/**
	 * Get files count,
	 * Accept pattern.
	 *
	 * @access public
	 * @param string $path
	 * @return mixed
	 */
	public static function count(string $path = '.')
	{
		if ( ($files = self::index($path)) ) {
			return (int)count($files);
		}
		return false;
	}

	/**
	 * Parse ini file.
	 *
	 * [Normal : 0]
	 * 
	 * @access public
	 * @param string $path
	 * @param bool $sections
	 * @param int $mode
	 * @return mixed
	 */
	public static function parseIni(string $path, bool $sections = false, int $mode = 0)
	{
		return parse_ini_file(
			Stringify::formatPath($path), $sections, $mode
		);
	}

	/**
	 * Import file from URL.
	 * 
	 * @access public
	 * @param string $url
	 * @param string $path
	 * @return bool
	 */
	public static function import(string $url, string $path) : bool
	{
		$tmp = @fopen($path, 'w');
		$status = @fwrite($tmp, (string)self::r($url));
		fclose($tmp);
		return (bool)$status;
	}

	/**
	 * Download file.
	 *
	 * @access public
	 * @param string $path
	 * @return mixed
	 */
	public static function download(string $path)
	{
		if ( self::exists($path) ) {
			$file = self::r($path);
			$filename = Stringify::replace(' ', '-', Stringify::basename($path));
			header('Content-type: application/force-download');
			header("Content-Disposition: attachment; filename={$filename};");
			echo $file;
			die();
		}
		return false;
	}

	/**
	 * Validate file mime type.
	 *
	 * @access public
	 * @param string $path
	 * @param array $mimes
	 * @return bool
	 */
	public static function validate(string $path, array $mimes = []) : bool
	{
		$mime = self::getMime($path, $mimes);
		return ($mime['type'] !== false);
	}

	/**
	 * Get file mime type.
	 *
	 * @access public
	 * @param string $path
	 * @param array $mimes
	 * @return array
	 */
	public static function getMime(string $path, array $mimes = []) : array
	{
		$filename = Stringify::basename($path);
		if ( empty($mimes) ) {
			$mimes = self::mimes();
		}

		$type = false;
		$ext  = false;

		foreach ( $mimes as $regex => $mime ) {
			$regex = '!\.(' . $regex . ')$!i';
			if ( ($match = Stringify::match($regex, $filename, 1)) ) {
				$type = $mime;
				$ext  = $match;
				break;
			}
		}
		return compact('type', 'ext');
	}

	/**
	 * Get default file mime types (Regex).
	 *
	 * @access public
	 * @return array
	 */
	public static function mimes() : array
	{
		return [
			'jpg|jpeg|jpe'       => 'image/jpeg',
			'gif'                => 'image/gif',
			'png'                => 'image/png',
			'bmp'                => 'image/bmp',
			'tiff|tif'           => 'image/tiff',
			'ico'                => 'image/x-icon',
			'txt'                => 'text/plain',
			'csv'                => 'text/csv',
			'tsv'                => 'text/tab-separated-values',
			'ics'                => 'text/calendar',
			'json'               => 'application/json',
			'xml'                => 'application/xml',
			'pdf'                => 'application/pdf',
			'zip'                => 'application/zip',
			'gz|gzip'            => 'application/x-gzip',
			'7z'                 => 'application/x-7z-compressed'
		];
	}
}
