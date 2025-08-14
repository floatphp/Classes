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

use \ZipArchive as ZIP;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;
use \Exception;

/**
 * Advanced archive manipulation.
 */
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
	 * @throws Exception
	 */
	public static function compress(string $path, string $to = '', string $archive = '') : bool
	{
		$path = Stringify::formatPath($path);

		if ( !TypeCheck::isClass('ZipArchive') ) {
			return false;
		}

		if ( !self::exists($path) ) {
			return false;
		}

		if ( empty($archive) ) {
			$archive = basename($path);
		}

		if ( empty($to) ) {
			$to = dirname($path);
		}

		$to = Stringify::formatPath($to, true);
		$archivePath = "{$to}/{$archive}.zip";

		// Ensure destination directory exists
		if ( !self::isDir($to) && !self::addDir($to) ) {
			return false;
		}

		$zip = new ZIP();

		if ( $zip->open($archivePath, ZIP::CREATE | ZIP::OVERWRITE) !== true ) {
			return false;
		}

		try {
			if ( self::isDir($path) ) {
				$files = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
					RecursiveIteratorIterator::LEAVES_ONLY
				);

				foreach ($files as $name => $file) {
					if ( !$file->isDir() ) {
						$filePath = $file->getRealPath();
						$relativePath = Stringify::replace($path . DIRECTORY_SEPARATOR, '', $filePath);
						$zip->addFile($filePath, $relativePath);
					}
				}

			} elseif ( self::isFile($path) ) {
				$zip->addFile($path, basename($path));
			}

			$result = $zip->close();
			return $result;

		} catch (Exception $e) {
			$zip->close();
			return false;
		}
	}

	/**
	 * Uncompress archive.
	 * 
	 * @access public
	 * @param string $archive
	 * @param string $to
	 * @param bool $remove
	 * @return bool
	 * @throws Exception
	 */
	public static function uncompress(string $archive, string $to = '', bool $remove = false) : bool
	{
		$archive = Stringify::formatPath($archive);
		$to = Stringify::formatPath($to);

		if ( !self::isFile($archive) ) {
			return false;
		}

		$status = false;

		if ( empty($to) ) {
			$to = dirname($archive);
		}

		// Ensure destination directory exists
		if ( !self::isDir($to) && !self::addDir($to) ) {
			return false;
		}

		try {
			if ( TypeCheck::isClass('ZipArchive') ) {
				$zip = new ZIP();
				$resource = $zip->open($archive);
				if ( $resource === true ) {
					$status = $zip->extractTo($to);
					$zip->close();
				}

			} elseif ( self::isGzip($archive) ) {
				$status = self::unGzip($archive, 4096, false);
			}

			if ( $status && $remove ) {
				self::remove($archive);
			}

			return $status;

		} catch (Exception $e) {
			return false;
		}
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
		if ( !self::isFile($archive) || self::getExtension($archive) !== 'gz' ) {
			return false;
		}

		// Check gzip magic number (1f 8b)
		$file = @fopen($archive, 'rb');
		if ( !$file ) {
			return false;
		}

		$header = @fread($file, 2);
		@fclose($file);

		if ( strlen($header) !== 2 ) {
			return false;
		}

		// Gzip files start with 0x1f 0x8b
		if ( ord($header[0]) !== 0x1f || ord($header[1]) !== 0x8b ) {
			return false;
		}

		// Additional validation: try to open with gzopen
		$gz = @gzopen($archive, 'r');
		if ( !$gz ) {
			return false;
		}

		$status = (bool)@gzread($gz, $length);
		@gzclose($gz);

		return $status;
	}

	/**
	 * Uncompress gzip archive.
	 * 
	 * @access public
	 * @param string $archive
	 * @param int $length
	 * @param bool $remove
	 * @return bool
	 * @throws Exception
	 */
	public static function unGzip(string $archive, int $length = 4096, bool $remove = false) : bool
	{
		if ( !self::isFile($archive) ) {
			return false;
		}

		$gz = @gzopen($archive, 'rb');
		if ( !$gz ) {
			return false;
		}

		$filename = Stringify::remove('.gz', $archive);
		$to = @fopen($filename, 'wb');

		if ( !$to ) {
			@gzclose($gz);
			return false;
		}

		try {
			while (!gzeof($gz)) {
				$data = @gzread($gz, $length);
				if ( $data === false ) {
					break;
				}
				@fwrite($to, $data);
			}

			$status = true;

		} catch (Exception $e) {
			$status = false;

		} finally {
			@fclose($to);
			@gzclose($gz);
		}

		if ( $status && $remove ) {
			self::remove($archive);
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

		if ( !TypeCheck::isClass('ZipArchive') || !self::isFile($archive) ) {
			return false;
		}

		$zip = new ZIP();

		try {
			if ( $zip->open($archive, ZIP::RDONLY) === true ) {
				$isValid = $zip->numFiles > 0;
				$zip->close();
				return $isValid;
			}
		} catch (Exception $e) {
			// Archive is corrupted or invalid
		}

		return false;
	}

	/**
	 * Get archive information.
	 * 
	 * @access public
	 * @param string $archive
	 * @return array|false
	 */
	public static function getInfo(string $archive) : array|false
	{
		$archive = Stringify::formatPath($archive);

		if ( !TypeCheck::isClass('ZipArchive') || !self::isFile($archive) ) {
			return false;
		}

		$zip = new ZIP();

		try {
			if ( $zip->open($archive, ZIP::RDONLY) === true ) {
				$info = [
					'filename' => basename($archive),
					'size'     => self::getFileSize($archive),
					'numFiles' => $zip->numFiles,
					'comment'  => $zip->comment,
					'files'    => []
				];

				for ($i = 0; $i < $zip->numFiles; $i++) {
					$stat = $zip->statIndex($i);
					if ( $stat !== false ) {
						$info['files'][] = [
							'name'            => $stat['name'],
							'size'            => $stat['size'],
							'compressed_size' => $stat['comp_size'],
							'mtime'           => $stat['mtime']
						];
					}
				}

				$zip->close();
				return $info;
			}
		} catch (Exception $e) {
			// Archive is corrupted or invalid
		}

		return false;
	}

	/**
	 * Extract specific file from archive.
	 * 
	 * @access public
	 * @param string $archive
	 * @param string $filename
	 * @param string $to
	 * @return bool
	 */
	public static function extractFile(string $archive, string $filename, string $to = '') : bool
	{
		$archive = Stringify::formatPath($archive);

		if ( !TypeCheck::isClass('ZipArchive') || !self::isFile($archive) ) {
			return false;
		}

		if ( empty($to) ) {
			$to = dirname($archive);
		}

		$zip = new ZIP();

		try {
			if ( $zip->open($archive, ZIP::RDONLY) === true ) {
				$content = $zip->getFromName($filename);
				$zip->close();

				if ( $content !== false ) {
					$destination = $to . DIRECTORY_SEPARATOR . basename($filename);
					return self::w($destination, $content);
				}
			}
		} catch (Exception $e) {
			// File extraction failed
		}

		return false;
	}
}
