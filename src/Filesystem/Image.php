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
	 * Maximum file size in bytes.
	 */
	private const MAXSIZE = 10485760;

	/**
	 * Supported image formats.
	 */
	private const FORMATS = [
		'image/jpeg' => 'jpeg',
		'image/png'  => 'png',
		'image/gif'  => 'gif',
		'image/webp' => 'webp',
		'image/bmp'  => 'bmp'
	];

	/**
	 * Quality settings by format.
	 */
	private const QUALITY = [
		'jpeg' => 85,
		'png'  => 9,
		'webp' => 80,
		'gif'  => null,
		'bmp'  => null
	];

	/**
	 * Resize image with comprehensive options.
	 * 
	 * @access public
	 * @param string $path Source image path
	 * @param int $width Target width
	 * @param int $height Target height
	 * @param array $options Resize options
	 * @return bool
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public static function resize(string $path, int $width = 50, int $height = 50, array $options = []) : bool
	{
		// Validate and sanitize input
		$path = self::validateImagePath($path);
		self::validateDimensions($width, $height);

		// Parse options with defaults
		$options = Arrayify::merge([
			'crop'            => false,
			'quality'         => null,
			'format'          => null, // Auto-detect if null
			'output'          => $path, // Overwrite original if not specified
			'maintain_aspect' => true,
			'upscale'         => false
		], $options);

		// Load source image
		$sourceImage = self::loadImage($path);
		$imageInfo = self::getImageInfo($path);

		// Calculate new dimensions
		$dimensions = self::calculateDimensions(
			$imageInfo['width'],
			$imageInfo['height'],
			$width,
			$height,
			$options
		);

		// Create resized image
		$resizedImage = self::createResizedImage($sourceImage, $imageInfo, $dimensions, $options);

		// Save image
		$result = self::saveImage($resizedImage, $options['output'], $imageInfo['format'], $options);

		// Cleanup
		self::destroyImage($sourceImage);
		self::destroyImage($resizedImage);

		return $result;
	}

	/**
	 * Create thumbnail with smart cropping.
	 *
	 * @access public
	 * @param string $path
	 * @param int $size
	 * @param string $output
	 * @return bool
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public static function thumbnail(string $path, int $size = 150, ?string $output = null) : bool
	{
		if ( $output === null ) {
			$info = pathinfo($path);
			$output = "{$info['dirname']}/{$info['filename']}_thumb.{$info['extension']}";
		}

		return self::resize($path, $size, $size, [
			'crop'    => true,
			'output'  => $output,
			'upscale' => false
		]);
	}

	/**
	 * Convert image format.
	 *
	 * @access public
	 * @param string $path
	 * @param string $format
	 * @param string $output
	 * @param array $options
	 * @return bool
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public static function convert(string $path, string $format, ?string $output = null, array $options = []) : bool
	{
		$path = self::validateImagePath($path);
		$format = Stringify::lowercase($format);

		if ( !self::isFormatSupported("image/{$format}") ) {
			throw new \InvalidArgumentException("Unsupported output format: {$format}");
		}

		if ( $output === null ) {
			$info = pathinfo($path);
			$output = "{$info['dirname']}/{$info['filename']}.{$format}";
		}

		$sourceImage = self::loadImage($path);
		$imageInfo = self::getImageInfo($path);

		$result = self::saveImage($sourceImage, $output, "image/{$format}", $options);
		self::destroyImage($sourceImage);

		return $result;
	}

	/**
	 * Rotate image.
	 *
	 * @access public
	 * @param string $path
	 * @param float $angle
	 * @param string $output
	 * @return bool
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public static function rotate(string $path, float $angle, ?string $output = null) : bool
	{
		$path = self::validateImagePath($path);

		if ( !TypeCheck::isFunction('imagerotate') ) {
			throw new \RuntimeException("GD imagerotate function not available");
		}

		$sourceImage = self::loadImage($path);
		$rotatedImage = imagerotate($sourceImage, $angle, 0);

		if ( $rotatedImage === false ) {
			self::destroyImage($sourceImage);
			throw new \RuntimeException("Failed to rotate image");
		}

		$imageInfo = self::getImageInfo($path);
		$output = $output ?? $path;

		$result = self::saveImage($rotatedImage, $output, $imageInfo['format'], []);

		self::destroyImage($sourceImage);
		self::destroyImage($rotatedImage);

		return $result;
	}

	/**
	 * Get image information.
	 *
	 * @access public
	 * @param string $path
	 * @return array
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public static function getInfo(string $path) : array
	{
		return self::getImageInfo(
			self::validateImagePath($path)
		);
	}

	/**
	 * Validate image mime type.
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
			'png'  => 'image/png',
			'gif'  => 'image/gif',
			'webp' => 'image/webp',
			'bmp'  => 'image/bmp'
		]);
	}

	/**
	 * Check if image format is supported.
	 *
	 * @access public
	 * @param string $format
	 * @return bool
	 */
	public static function isFormatSupported(string $format) : bool
	{
		return Arrayify::hasKey($format, self::FORMATS);
	}

	/**
	 * Get supported formats.
	 *
	 * @access public
	 * @return array
	 */
	public static function getSupportedFormats() : array
	{
		return Arrayify::keys(self::FORMATS);
	}

	/**
	 * Validate image path and security.
	 *
	 * @access private
	 * @param string $path
	 * @return string
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	private static function validateImagePath(string $path) : string
	{
		$path = Stringify::formatPath($path);

		if ( empty($path) ) {
			throw new \InvalidArgumentException("Image path cannot be empty");
		}

		if ( !File::exists($path) ) {
			throw new \InvalidArgumentException("Image file does not exist: {$path}");
		}

		if ( !File::isReadable($path) ) {
			throw new \RuntimeException("Image file is not readable: {$path}");
		}

		// Check file size
		$size = File::getFileSize($path);
		if ( $size > self::MAXSIZE ) {
			throw new \RuntimeException("Image file too large: {$size} bytes (max: " . self::MAXSIZE . ")");
		}

		// Validate MIME type
		if ( !self::isMime($path) ) {
			throw new \InvalidArgumentException("Invalid image format: {$path}");
		}

		return $path;
	}

	/**
	 * Validate image dimensions.
	 *
	 * @access private
	 * @param int $width
	 * @param int $height
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	private static function validateDimensions(int $width, int $height) : void
	{
		if ( $width <= 0 || $height <= 0 ) {
			throw new \InvalidArgumentException("Dimensions must be positive integers");
		}

		if ( $width > 10000 || $height > 10000 ) {
			throw new \InvalidArgumentException("Dimensions too large (max: 10000x10000)");
		}
	}

	/**
	 * Load image from file.
	 *
	 * @access private
	 * @param string $path
	 * @return \GdImage
	 * @throws \RuntimeException
	 */
	private static function loadImage(string $path) : \GdImage
	{
		$imageInfo = self::getImageInfo($path);
		$format = self::FORMATS[$imageInfo['format']];

		$image = match ($format) {
			'jpeg'  => @imagecreatefromjpeg($path),
			'png'   => @imagecreatefrompng($path),
			'gif'   => @imagecreatefromgif($path),
			'webp'  => TypeCheck::isFunction('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
			'bmp'   => TypeCheck::isFunction('imagecreatefrombmp') ? @imagecreatefrombmp($path) : @imagecreatefromwbmp($path),
			default => false
		};

		if ( $image === false ) {
			throw new \RuntimeException("Failed to load image: {$path}");
		}

		return $image;
	}

	/**
	 * Get comprehensive image information.
	 *
	 * @access private
	 * @param string $path
	 * @return array
	 * @throws \RuntimeException
	 */
	private static function getImageInfo(string $path) : array
	{
		$info = @getimagesize($path);
		if ( $info === false ) {
			throw new \RuntimeException("Failed to get image information: {$path}");
		}

		$mime = $info['mime'];
		if ( !self::isFormatSupported($mime) ) {
			throw new \RuntimeException("Unsupported image format: {$mime}");
		}

		return [
			'width'    => $info[0],
			'height'   => $info[1],
			'format'   => $mime,
			'channels' => $info['channels'] ?? null,
			'bits'     => $info['bits'] ?? null
		];
	}

	/**
	 * Calculate target dimensions with various options.
	 *
	 * @access private
	 * @param int $srcWidth
	 * @param int $srcHeight
	 * @param int $targetWidth
	 * @param int $targetHeight
	 * @param array $options
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	private static function calculateDimensions(int $srcWidth, int $srcHeight, int $targetWidth, int $targetHeight, array $options) : array
	{
		if ( $options['crop'] ) {
			return self::calculateCropDimensions($srcWidth, $srcHeight, $targetWidth, $targetHeight, $options);
		} else {
			return self::calculateResizeDimensions($srcWidth, $srcHeight, $targetWidth, $targetHeight, $options);
		}
	}

	/**
	 * Calculate crop dimensions.
	 *
	 * @access private
	 * @param int $srcWidth
	 * @param int $srcHeight
	 * @param int $targetWidth
	 * @param int $targetHeight
	 * @param array $options
	 * @return array
	 */
	private static function calculateCropDimensions(int $srcWidth, int $srcHeight, int $targetWidth, int $targetHeight, array $options) : array
	{
		if ( !$options['upscale'] && ($srcWidth < $targetWidth || $srcHeight < $targetHeight) ) {
			throw new \InvalidArgumentException("Source image too small for crop without upscaling");
		}

		$ratio = max($targetWidth / $srcWidth, $targetHeight / $srcHeight);
		$cropWidth = $targetWidth / $ratio;
		$cropHeight = $targetHeight / $ratio;
		$cropX = ($srcWidth - $cropWidth) / 2;
		$cropY = ($srcHeight - $cropHeight) / 2;

		return [
			'dst_width'  => $targetWidth,
			'dst_height' => $targetHeight,
			'src_width'  => (int)$cropWidth,
			'src_height' => (int)$cropHeight,
			'src_x'      => (int)$cropX,
			'src_y'      => (int)$cropY,
			'dst_x'      => 0,
			'dst_y'      => 0
		];
	}

	/**
	 * Calculate resize dimensions.
	 *
	 * @access private
	 * @param int $srcWidth
	 * @param int $srcHeight
	 * @param int $targetWidth
	 * @param int $targetHeight
	 * @param array $options
	 * @return array
	 */
	private static function calculateResizeDimensions(int $srcWidth, int $srcHeight, int $targetWidth, int $targetHeight, array $options) : array
	{
		if ( !$options['upscale'] && $srcWidth < $targetWidth && $srcHeight < $targetHeight ) {
			// Don't upscale - use original dimensions
			$targetWidth = $srcWidth;
			$targetHeight = $srcHeight;

		} elseif ( $options['maintain_aspect'] ) {
			$ratio = min($targetWidth / $srcWidth, $targetHeight / $srcHeight);
			$targetWidth = (int)($srcWidth * $ratio);
			$targetHeight = (int)($srcHeight * $ratio);
		}

		return [
			'dst_width'  => $targetWidth,
			'dst_height' => $targetHeight,
			'src_width'  => $srcWidth,
			'src_height' => $srcHeight,
			'src_x'      => 0,
			'src_y'      => 0,
			'dst_x'      => 0,
			'dst_y'      => 0
		];
	}

	/**
	 * Create resized image resource.
	 *
	 * @access private
	 * @param \GdImage $sourceImage
	 * @param array $imageInfo
	 * @param array $dimensions
	 * @param array $options
	 * @return \GdImage
	 * @throws \RuntimeException
	 */
	private static function createResizedImage(\GdImage $sourceImage, array $imageInfo, array $dimensions, array $options) : \GdImage
	{
		$resizedImage = imagecreatetruecolor($dimensions['dst_width'], $dimensions['dst_height']);

		if ( $resizedImage === false ) {
			throw new \RuntimeException("Failed to create image canvas");
		}

		// Handle transparency
		self::preserveTransparency($resizedImage, $imageInfo['format']);

		// Perform the resize/crop
		$success = imagecopyresampled(
			$resizedImage,
			$sourceImage,
			$dimensions['dst_x'],
			$dimensions['dst_y'],
			$dimensions['src_x'],
			$dimensions['src_y'],
			$dimensions['dst_width'],
			$dimensions['dst_height'],
			$dimensions['src_width'],
			$dimensions['src_height']
		);

		if ( !$success ) {
			self::destroyImage($resizedImage);
			throw new \RuntimeException("Failed to resize image");
		}

		return $resizedImage;
	}

	/**
	 * Preserve image transparency.
	 *
	 * @access private
	 * @param \GdImage $image
	 * @param string $format
	 * @return void
	 */
	private static function preserveTransparency(\GdImage $image, string $format) : void
	{
		if ( $format === 'image/png' || $format === 'image/gif' ) {
			imagecolortransparent($image, imagecolorallocatealpha($image, 0, 0, 0, 127));
			imagealphablending($image, false);
			imagesavealpha($image, true);
		}
	}

	/**
	 * Save image to file.
	 *
	 * @access private
	 * @param \GdImage $image
	 * @param string $output
	 * @param string $format
	 * @param array $options
	 * @return bool
	 * @throws \RuntimeException
	 */
	private static function saveImage(\GdImage $image, string $output, string $format, array $options) : bool
	{
		// Ensure output directory exists
		$outputDir = dirname($output);
		if ( !File::isDir($outputDir) ) {
			File::addDir($outputDir, 0755, true);
		}

		// Get quality setting
		$quality = $options['quality'] ?? self::QUALITY[self::FORMATS[$format]];
		$formatKey = self::FORMATS[$format];

		$result = match ($formatKey) {
			'jpeg'  => imagejpeg($image, $output, $quality),
			'png'   => imagepng($image, $output, $quality),
			'gif'   => imagegif($image, $output),
			'webp'  => TypeCheck::isFunction('imagewebp') ? imagewebp($image, $output, $quality) : false,
			'bmp'   => TypeCheck::isFunction('imagebmp') ? imagebmp($image, $output) : false,
			default => false
		};

		if ( !$result ) {
			throw new \RuntimeException("Failed to save image: {$output}");
		}

		return true;
	}

	/**
	 * Safely destroy image resource.
	 *
	 * @access private
	 * @param \GdImage|null $image
	 * @return void
	 */
	private static function destroyImage(?\GdImage $image) : void
	{
		if ( $image !== null ) {
			@imagedestroy($image);
		}
	}
}
