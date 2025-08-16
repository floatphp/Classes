<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file is a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

/**
 * Advanced file manipulation.
 */
class File
{
	/**
	 * Analyse file.
	 *
	 * @access public
	 * @param string $path
	 * @return array
	 */
	public static function analyse(string $path) : array
	{
		return [
			'parent'      => self::getParentDir($path),
			'name'        => self::getName($path),
			'filename'    => self::getFileName($path),
			'extension'   => self::getExtension($path),
			'accessed'    => self::getLastAccess($path),
			'changed'     => self::getLastChange($path),
			'size'        => self::getSize($path),
			'permissions' => self::getPermissions($path),
			'type'        => self::getMimeType($path)
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
		return dirname(
			Stringify::formatPath($path)
		);
	}

	/**
	 * Get file extension.
	 * [PATHINFO_EXTENSION: 4].
	 *
	 * @access public
	 * @param string $path
	 * @param bool $format
	 * @return string
	 */
	public static function getExtension(string $path, bool $format = true) : string
	{
		$path = Stringify::formatPath($path);
		$ext = pathinfo($path, 4);
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
	public static function getLastAccess(string $path) : mixed
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
	public static function getLastChange(string $path) : mixed
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
	public static function getSize(string $path, int $decimals = 2) : string
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
	public static function getPermissions(string $path, bool $convert = false) : mixed
	{
		$permissions = substr(sprintf('%o', @fileperms($path)), -4);
		return $convert ? intval($permissions) : $permissions;
	}

	/**
	 * Get all file lines.
	 * [ignore-new: 2].
	 * [skip-empty: 4].
	 *
	 * @access public
	 * @param string $path
	 * @param int $flags
	 * @return array
	 */
	public static function getLines(string $path, int $flags = 2 | 4) : array
	{
		$lines = [];

		if ( !self::isFile($path) && !self::isEmpty($path) ) {
			return $lines;
		}

		if ( ($buffer = @file($path, $flags)) ) {
			$lines = $buffer;
		}

		return $lines;
	}

	/**
	 * Parse file lines using stream.
	 *
	 * @access public
	 * @param string $path
	 * @param int $limit
	 * @return array
	 */
	public static function parseLines(string $path, int $limit = 10) : array
	{
		$lines = [];

		if ( !self::isFile($path) && !self::isEmpty($path) ) {
			return $lines;
		}

		if ( !$limit ) {
			$limit = 1;
		}

		if ( ($handle = fopen($path, 'r')) ) {
			$offset = 0;
			while ((($line = fgets($handle)) !== false) && ($offset < $limit)) {
				$offset++;
				$lines[] = $line;
			}
			fclose($handle);
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
	public static function addString(string $path, string $input = '') : void
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
	public static function addBreak(string $path) : void
	{
		$handle = @fopen($path, 'a');
		@fwrite($handle, Stringify::break());
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
		$path = self::validatePath($path);
		$to = self::validatePath($to);
		if ( empty($path) || empty($to) ) {
			return false;
		}

		$dir = dirname($to);
		if ( self::exists($path) && self::isDir($dir) ) {
			return copy($path, $to, $context);
		}

		return false;
	}

	/**
	 * Copy file with exception handling.
	 *
	 * @access public
	 * @param string $path
	 * @param string $to
	 * @param resource $context
	 * @return bool
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public static function copySafe(string $path, string $to, $context = null) : bool
	{
		$path = self::validatePath($path, true);
		$to = self::validatePath($to, true);

		if ( !self::exists($path) ) {
			throw new \RuntimeException("Source file does not exist: {$path}");
		}

		if ( !self::isReadable($path) ) {
			throw new \RuntimeException("Source file is not readable: {$path}");
		}

		$dir = dirname($to);
		if ( !self::isDir($dir) ) {
			throw new \RuntimeException("Destination directory does not exist: {$dir}");
		}

		if ( !self::isWritable($dir) ) {
			throw new \RuntimeException("Destination directory is not writable: {$dir}");
		}

		if ( self::exists($to) && !self::isWritable($to) ) {
			throw new \RuntimeException("Destination file is not writable: {$to}");
		}

		$result = copy($path, $to, $context);
		if ( !$result ) {
			throw new \RuntimeException("Failed to copy file from {$path} to {$to}");
		}

		return true;
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
		$path = self::validatePath($path);
		$to = self::validatePath($to);
		if ( empty($path) || empty($to) ) {
			return false;
		}

		$dir = dirname($to);
		if ( self::exists($path) && self::isDir($dir) ) {
			return rename($path, $to, $context);
		}

		return false;
	}

	/**
	 * Move file with exception handling.
	 *
	 * @access public
	 * @param string $path
	 * @param string $to
	 * @param resource $context
	 * @return bool
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public static function moveSafe(string $path, string $to, $context = null) : bool
	{
		$path = self::validatePath($path, true);
		$to = self::validatePath($to, true);

		if ( !self::exists($path) ) {
			throw new \RuntimeException("Source file does not exist: {$path}");
		}

		if ( !self::isWritable(dirname($path)) ) {
			throw new \RuntimeException("Source directory is not writable: " . dirname($path));
		}

		$dir = dirname($to);
		if ( !self::isDir($dir) ) {
			throw new \RuntimeException("Destination directory does not exist: {$dir}");
		}

		if ( !self::isWritable($dir) ) {
			throw new \RuntimeException("Destination directory is not writable: {$dir}");
		}

		if ( self::exists($to) ) {
			throw new \RuntimeException("Destination file already exists: {$to}");
		}

		$result = rename($path, $to, $context);
		if ( !$result ) {
			throw new \RuntimeException("Failed to move file from {$path} to {$to}");
		}

		return true;
	}

	/**
	 * Check file.
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
	public static function isEmpty(string $path) : mixed
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
		$path = self::validatePath($path);
		if ( empty($path) ) {
			return false;
		}

		if ( !self::isFile($path) && !self::isDir($path) ) {
			if ( TypeCheck::isResource($c) ) {
				return @mkdir($path, $p, $r, $c);
			}
			return @mkdir($path, $p, $r);
		}
		return false;
	}

	/**
	 * Add directory with exception handling.
	 *
	 * @access public
	 * @param string $path
	 * @param int $p permissions
	 * @param bool $r recursive
	 * @param resource $c context
	 * @return bool
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public static function addDirSafe(string $path, int $p = 0755, bool $r = true, $c = null) : bool
	{
		$path = self::validatePath($path, true);

		if ( self::exists($path) ) {
			if ( self::isDir($path) ) {
				return true; // Directory already exists
			}
			throw new \RuntimeException("Path exists but is not a directory: {$path}");
		}

		// Check parent directory permissions
		$parent = dirname($path);
		if ( !self::isDir($parent) && !$r ) {
			throw new \RuntimeException("Parent directory does not exist: {$parent}");
		}

		if ( self::exists($parent) && !self::isWritable($parent) ) {
			throw new \RuntimeException("Parent directory is not writable: {$parent}");
		}

		$result = TypeCheck::isResource($c) ?
			@mkdir($path, $p, $r, $c) :
			@mkdir($path, $p, $r);

		if ( !$result ) {
			throw new \RuntimeException("Failed to create directory: {$path}");
		}

		return true;
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
		if ( !self::isDir($path) ) {
			return false;
		}

		$handle = @opendir($path);
		if ( !TypeCheck::isResource($handle) ) {
			return false;
		}

		while ($file = readdir($handle)) {
			if ( $file !== '.' && $file !== '..' ) {
				if ( !self::isDir("{$path}/{$file}") ) {
					self::remove("{$path}/{$file}");
				} else {
					$dir = "{$path}/{$file}";
					foreach (@scandir($dir) as $file) {
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

		closedir($handle);
		return true;
	}

	/**
	 * Directory recursive remove.
	 * 
	 * @access private
	 * @param string $path
	 * @return void
	 */
	private static function recursiveRemove(string $path) : void
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
	 * Check whether path exists (file|directory).
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
	 * @param bool $i Include path
	 * @param ?resource $c Context
	 * @param int $o Offset
	 * @param ?int $l Length
	 * @return mixed
	 */
	public static function r(string $path, bool $i = false, $c = null, int $o = 0, ?int $l = null) : mixed
	{
		$path = self::validatePath($path);
		if ( empty($path) ) {
			return false;
		}
		return @file_get_contents($path, $i, $c, $o, $l);
	}

	/**
	 * Read file with exception handling.
	 *
	 * @access public
	 * @param string $path
	 * @param bool $i Include path
	 * @param ?resource $c Context
	 * @param int $o Offset
	 * @param ?int $l Length
	 * @return string
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public static function readSafe(string $path, bool $i = false, $c = null, int $o = 0, ?int $l = null) : string
	{
		$path = self::validatePath($path, true);

		if ( !self::exists($path) ) {
			throw new \RuntimeException("File does not exist: {$path}");
		}

		if ( !self::isReadable($path) ) {
			throw new \RuntimeException("File is not readable: {$path}");
		}

		$content = @file_get_contents($path, $i, $c, $o, $l);
		if ( $content === false ) {
			throw new \RuntimeException("Failed to read file: {$path}");
		}

		return $content;
	}

	/**
	 * Write file.
	 *
	 * @access public
	 * @param string $path
	 * @param mixed $input
	 * @param bool $append
	 * @param ?resource $c Context
	 * @return bool
	 */
	public static function w(string $path, $input = '', bool $append = false, $c = null) : bool
	{
		$path = self::validatePath($path);
		if ( empty($path) ) {
			return false;
		}

		$flag = 0;
		if ( $append ) {
			$flag = FILE_APPEND;
			$input .= Stringify::break();
		}
		return (bool)@file_put_contents($path, $input, $flag, $c);
	}

	/**
	 * Write file with exception handling.
	 *
	 * @access public
	 * @param string $path
	 * @param mixed $input
	 * @param bool $append
	 * @param ?resource $c Context
	 * @return int
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public static function writeSafe(string $path, $input = '', bool $append = false, $c = null) : int
	{
		$path = self::validatePath($path, true);

		// Check if directory exists and is writable
		$dir = dirname($path);
		if ( !self::isDir($dir) ) {
			throw new \RuntimeException("Directory does not exist: {$dir}");
		}

		if ( !self::isWritable($dir) ) {
			throw new \RuntimeException("Directory is not writable: {$dir}");
		}

		// Check if file exists and is writable
		if ( self::exists($path) && !self::isWritable($path) ) {
			throw new \RuntimeException("File is not writable: {$path}");
		}

		$flag = 0;
		if ( $append ) {
			$flag = FILE_APPEND;
			$input .= Stringify::break();
		}

		$result = @file_put_contents($path, $input, $flag, $c);
		if ( $result === false ) {
			throw new \RuntimeException("Failed to write file: {$path}");
		}

		return $result;
	}

	/**
	 * Read file using stream.
	 *
	 * @access public
	 * @param string $path
	 * @return mixed
	 */
	public static function read(string $path) : mixed
	{
		if ( self::exists($path) ) {
			if ( ($handle = fopen($path, 'r')) ) {
				$size = self::getFileSize($path);
				$content = fread($handle, $size);
				fclose($handle);
				return $content;
			}
		}
		return false;
	}

	/**
	 * Scan directory,
	 * [ASC: 0],
	 * [DESC: 1],
	 * [NO: 2].
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
	public static function index(string $path = '.') : mixed
	{
		if ( Stringify::contains($path, '*') ) {
			$dir = dirname($path);
		} else {
			$dir = $path;
			$path = Stringify::formatPath("{$path}/*.*");
		}
		if ( self::isDir($dir) ) {
			$files = glob($path);
			return Arrayify::combine(
				$files,
				Arrayify::map('filectime', $files)
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
	public static function last(string $path = '.') : mixed
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
	public static function first(string $path = '.') : mixed
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
	public static function count(string $path = '.') : mixed
	{
		if ( ($files = self::index($path)) ) {
			return (int)count($files);
		}
		return false;
	}

	/**
	 * Parse ini file.
	 *
	 * [INI_SCANNER_NORMAL    : 0].
	 * [FILE_IGNORE_NEW_LINES : 2].
	 * [FILE_SKIP_EMPTY_LINES : 4].
	 *
	 * @access public
	 * @param string $path
	 * @param bool $sections
	 * @param int $mode
	 * @return mixed
	 */
	public static function parseIni(string $path, bool $sections = false, int $mode = 0) : mixed
	{
		$path = Stringify::formatPath($path);

		if ( TypeCheck::isFunction('parse-ini-file') ) {
			return parse_ini_file($path, $sections, $mode);
		}

		if ( !self::exists($path) || !self::isReadable($path) ) {
			throw new \RuntimeException("File not found or not readable: {$path}");
		}

		$lines = file($path, 2 | 4);
		$data = [];
		$section = null;

		foreach ($lines as $line) {
			$line = trim($line);

			// Skip comments and empty lines
			if ( $line === '' || $line[0] === ';' || $line[0] === '#' ) {
				continue;
			}

			// Remove trailing semicolon
			if ( substr($line, -1) === ';' ) {
				$line = substr($line, 0, -1);
			}

			// Process sections
			if ( $line[0] === '[' && substr($line, -1) === ']' ) {
				if ( $sections ) {
					$section = substr($line, 1, -1);
					$data[$section] = [];
				}
				continue;
			}

			// Process key-value pairs
			$keyValue = explode('=', $line, 2);
			if ( count($keyValue) !== 2 ) {
				throw new \RuntimeException("Invalid line in INI file: {$line}");
			}

			[$key, $value] = array_map('trim', $keyValue);

			// Parse booleans, null, and numbers
			if ( strtolower($value) === 'true' ) {
				$value = true;

			} elseif ( strtolower($value) === 'false' ) {
				$value = false;

			} elseif ( strtolower($value) === 'null' ) {
				$value = null;

			} elseif ( is_numeric($value) ) {
				// Convert to int or float
				$value = $value + 0;

			} else {
				// Remove quotes if present
				$value = trim($value, '"\'');
			}

			if ( $sections && $section !== null ) {
				$data[$section][$key] = $value;

			} else {
				$data[$key] = $value;
			}
		}

		return $data;
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
	 * @param bool $remove
	 * @param string $timeout
	 * @param bool $verify
	 * @return mixed
	 */
	public static function download(string $path) : mixed
	{
		if ( self::exists($path) ) {
			$file = self::r($path);
			$filename = Stringify::replace(search: ' ', replace: '-', subject: Stringify::basename($path));
			header('Content-type: application/force-download');
			header("Content-Disposition: attachment; filename={$filename};");
			echo $file;
			exit();
		}
		return false;
	}

	/**
	 * Get file mime type.
	 * [FILEINFO_MIME_TYPE: 16].
	 *
	 * @access public
	 * @param string $path
	 * @param string $ext
	 * @param array $types
	 * @return string
	 */
	public static function getMimeType(string $path, ?string $ext = null, ?array $types = []) : string
	{
		if ( TypeCheck::isClass('\finfo') ) {
			return (new \finfo(16))->file($path) ?: 'undefined';
		}

		if ( !$ext ) {
			$ext = self::getExtension($path);
		}

		foreach ($types as $match => $type) {
			$match = explode('|', $match);
			if ( Arrayify::inArray($ext, $match) ) {
				return $type;
			}
		}

		return 'undefined';
	}

	/**
	 * Get file allowed mime types.
	 *
	 * @access public
	 * @return array
	 */
	public static function getMimes() : array
	{
		return [
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif'          => 'image/gif',
			'png'          => 'image/png',
			'bmp'          => 'image/bmp',
			'tiff|tif'     => 'image/tiff',
			'ico'          => 'image/x-icon',
			'txt'          => 'text/plain',
			'csv'          => 'text/csv',
			'tsv'          => 'text/tab-separated-values',
			'ics'          => 'text/calendar',
			'json'         => 'application/json',
			'xml'          => 'application/xml',
			'pdf'          => 'application/pdf',
			'zip'          => 'application/zip',
			'gz|gzip'      => 'application/x-gzip',
			'7z'           => 'application/x-7z-compressed'
		];
	}

	/**
	 * Validate and sanitize file path against directory traversal attacks.
	 *
	 * @access public
	 * @param string $path
	 * @param bool $throwException
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public static function validatePath(string $path, bool $throwException = false) : string
	{
		// Normalize path
		$path = Stringify::formatPath($path);

		// Check for directory traversal patterns
		$dangerous = ['../', '..\/', '../', '..\\'];
		foreach ($dangerous as $pattern) {
			if ( Stringify::contains($path, $pattern) ) {
				if ( $throwException ) {
					throw new \InvalidArgumentException("Path contains directory traversal: {$path}");
				}
				return '';
			}
		}

		// Check for null bytes (path truncation attack)
		if ( Stringify::contains($path, "\0") ) {
			if ( $throwException ) {
				throw new \InvalidArgumentException("Path contains null byte: {$path}");
			}
			return '';
		}

		return $path;
	}
}
