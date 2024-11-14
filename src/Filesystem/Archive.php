<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.3.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

use \ZipArchive as ZIP;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

final class Archive extends File
{
	/**
	 * Compress archive.
	 * 
	 * @access public
	 * @param string $path
	 * @param string $to
	 * @param string $archive
	 * @return bool
	 */
	public static function compress(string $path, string $to = '', string $archive = '') : bool
	{
		$path = Stringify::formatPath($path);

		if ( TypeCheck::isClass('ZipArchive') && self::exists($path) ) {

			if ( empty($archive) ) {
				$archive = basename($path);
			}

			if ( empty($to) ) {
				$to = dirname($path);
			}

			$to = Stringify::formatPath($to, true);
			$to = "{$to}/{$archive}.zip";
			$zip = new ZIP();

			if ( $zip->open($to, ZIP::CREATE | ZIP::OVERWRITE) ) {
				if ( self::isDir($path) ) {
					$files = new RecursiveIteratorIterator(
						new RecursiveDirectoryIterator($path),
						RecursiveIteratorIterator::LEAVES_ONLY
					);
					foreach ($files as $name => $file) {
						if ( !$file->isDir() ) {
							$p = $file->getRealPath();
							$zip->addFile($p, basename($name));
						}
					}
				} elseif ( self::isFile($path) ) {
					$zip->addFile($path, basename($path));
				}
				$zip->close();
				return true;
			}
		}

		return false;
	}

	/**
	 * Uncompress archive.
	 * 
	 * @access public
	 * @param string $archive
	 * @param string $to
	 * @param bool $remove
	 * @return bool
	 */
	public static function uncompress(string $archive, string $to = '', bool $remove = false) : bool
	{
		$archive = Stringify::formatPath($archive);
		$to = Stringify::formatPath($to);

		if ( self::isFile($archive) ) {

			$status = false;

			if ( empty($to) ) {
				$to = dirname($archive);
			}

			if ( TypeCheck::isClass('ZipArchive') ) {
				$zip = new ZIP();
				$resource = $zip->open($archive);
				if ( $resource === true ) {
					$zip->extractTo($to);
					$zip->close();
					$status = true;
				}
			} elseif ( self::isGzip($archive) ) {
				$status = self::unGzip($archive);
			}

			if ( $remove ) {
				self::remove($archive);
			}

			return $status;
		}

		return false;
	}

	/**
	 * Check for valid gzip archive.
	 * 
	 * @access public
	 * @param string $archive
	 * @param int $length
	 * @return bool
	 */
	public static function isGzip(string $archive, int $length = 4096) : bool
	{
		if ( self::isFile($archive) && self::getExtension($archive) == 'gz' ) {
			$status = false;
			if ( ($gz = gzopen($archive, 'r')) ) {
				$status = (bool)gzread($gz, $length);
			}
			gzclose($gz);
			return $status;
		}
		return false;
	}

	/**
	 * Uncompress gzip archive.
	 * 
	 * @access public
	 * @param string $archive
	 * @param int $length
	 * @param bool $remove
	 * @return bool
	 */
	public static function unGzip(string $archive, int $length = 4096, bool $remove = false) : bool
	{
		$status = false;
		if ( self::isFile($archive) ) {
			if ( ($gz = gzopen($archive, 'rb')) ) {
				$filename = Stringify::remove('.gz', $archive);
				$to = fopen($filename, 'wb');
				while (!gzeof($gz)) {
					fwrite($to, gzread($gz, $length));
				}
				$status = true;
				fclose($to);
			}
			gzclose($gz);
			if ( $remove ) {
				self::remove($archive);
			}
		}
		return $status;
	}

	/**
	 * Validate ZIP archive.
	 * 
	 * @access public
	 * @param string $archive
	 * @return bool
	 */
	public static function isValid(string $archive) : bool
	{
		$archive = Stringify::formatPath($archive);
		if ( TypeCheck::isClass('ZipArchive') && self::isFile($archive) ) {
			$zip = new ZIP();
			if ( $zip->open($archive) === true ) {
				if ( $zip->numFiles ) {
					$zip->close();
					return true;
				}
			}
		}
		return false;
	}
}
