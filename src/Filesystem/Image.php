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
 * Advanced image manipulation.
 */
final class Image
{
	/**
	 * Resize image.
	 * 
	 * @access public
	 * @param string $path
	 * @param int $width
	 * @param int $height
	 * @param bool $crop
	 * @return bool
	 */
	public static function resize(string $path, int $width = 50, int $height = 50, bool $crop = false) : bool
	{
		$type = @mime_content_type($path);
		$allowed = [
			'image/png',
			'image/jpeg',
			'image/bmp',
			'image/gif'
		];

		// Check allowed types
		if ( !Arrayify::inArray($type, $allowed) ) {
			return false;
		}

		// Check info
		if ( !($info = getimagesize($path)) ) {
			return false;
		}

		// Set source
		if ( $info['mime'] == 'image/png' ) {
			$src = @imagecreatefrompng($path);

		} elseif ( $info['mime'] == 'image/jpeg' ) {
			$src = @imagecreatefromjpeg($path);

		} elseif ( $info['mime'] == 'image/bmp' ) {
			$src = @imagecreatefromwbmp($path);

		} elseif ( $info['mime'] == 'image/gif' ) {
			$src = @imagecreatefromgif($path);
		}

		// Check source
		if ( !$src ) {
			return false;
		}

		// Calculate sizes
		$srcWidth = $info[0];
		$srcHeight = $info[1];

		if ( $crop ) {
			if ( $srcWidth < $width or $srcHeight < $height ) {
				return false;
			}
			$ratio = max($width / $srcWidth, $height / $srcHeight);
			$srcHeight = $height / $ratio;
			$x = ($srcWidth - $width / $ratio) / 2;
			$srcWidth = $width / $ratio;
		} else {
			if ( $srcWidth < $width and $srcHeight < $height ) {
				return false;
			}
			$ratio = min($width / $srcWidth, $height / $srcHeight);
			$width = $srcWidth * $ratio;
			$height = $srcHeight * $ratio;
			$x = 0;
		}

		// Create
		$image = imagecreatetruecolor((int)$width, (int)$height);

		// Transparency
		if ( $info['mime'] == 'image/png' || $info['mime'] == 'image/gif' ) {
			imagecolortransparent($image, imagecolorallocatealpha($image, 0, 0, 0, 127));
			imagealphablending($image, false);
			imagesavealpha($image, true);
		}

		// Generate
		imagecopyresampled(
			$image,
			$src,
			0,
			0,
			(int)$x,
			0,
			(int)$width,
			(int)$height,
			(int)$srcWidth,
			(int)$srcHeight
		);

		// Save (PNG)
		return imagepng($image, Stringify::lowercase($path), quality: 0);
	}

	/**
	 * Validate image mime.
	 *
	 * @access public
	 * @param string $file
	 * @return bool
	 */
	public static function isMime(string $file) : bool
	{
		return Validator::isMime($file, [
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'bmp'  => 'image/bmp',
			'png'  => 'image/png',
			'gif'  => 'image/gif'
		]);
	}
}
