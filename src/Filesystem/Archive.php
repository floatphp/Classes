<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component
 * @version    : 1.0.0
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2022 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Filesystem;

use \ZipArchive as ZIP;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

final class Archive
{
	/**
	 * @access public
	 * @param string $path
	 * @param string $to
	 * @param string $archive
	 * @return bool
	 */
	public static function compress($path, $to = '', $archive = '') : bool
	{
		if ( !empty($path) ) {
			if ( empty($archive) ) {
				$archive = basename($path);
			}
			if ( empty($to) ) {
				$to = dirname($path);
			}
			$to = Stringify::formatPath($to,true);
			$to = "{$to}/{$archive}.zip";
			$zip = new ZIP();
			if ( $zip->open($to, ZIP::CREATE | ZIP::OVERWRITE) ) {
				if ( File::isDir($path) ) {
					$files = new RecursiveIteratorIterator(
					    new RecursiveDirectoryIterator($path),
					    RecursiveIteratorIterator::LEAVES_ONLY
					);
					foreach ($files as $name => $file) {
					    if ( !$file->isDir() ){
					        $p = $file->getRealPath();
					        $zip->addFile($p,basename($name));
					    }
					}
				} elseif ( File::isFile($path) ) {
					$zip->addFile($path,basename($path));
				}
				$zip->close();
				return true;
			}
		}
		return false;
	}

	/**
	 * @access public
	 * @param string $archive
	 * @param string $to
	 * @param bool $clear
	 * @return bool
	 */
	public static function uncompress($archive, $to = '', $clear = true) : bool
	{
		if ( File::exists($archive) ) {
			$zip = new ZIP();
			if ( empty($to) ) {
				$to = dirname($archive);
			}
			if ( $zip->open($archive) === true ) {
				if ( $zip->numFiles ) {
			  		$zip->extractTo($to);
			  		$zip->close();
			  		if ( $clear ) {
			  			@unlink($archive);
			  		}
			  		return true;
				}
			}
		}
		return false;
	}

	/**
	 * @access public
	 * @param string $archive
	 * @return bool
	 */
	public static function isGzip($archive) : bool
	{
		if ( File::isFile($archive) ) {
			if ( File::getExtension($archive) == 'gz' ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @access public
	 * @param string $archive
	 * @param int $buffer
	 * @return bool
	 */
	public static function unGzip($archive, $buffer = 4096) : bool
	{
		$status = false;
		if ( ($gz = gzopen($archive,'rb')) ) {
			$filename = Stringify::replace('.gz','',$archive);
			$to = fopen($filename,'wb');
			while ( !gzeof($gz) ) {
			    fwrite($to,gzread($gz,$buffer));
			}
			$status = true;
			fclose($to);
		}
		gzclose($gz);
		return $status;
	}

	/**
	 * @access public
	 * @param string $archive
	 * @return bool
	 */
	public static function isValid($archive) : bool
	{
		$zip = new ZIP();
		if ( $zip->open($archive) === true ) {
			if ( $zip->numFiles ) {
		  		$zip->close();
		  		return true;
			}
		}
		return false;
	}
}
