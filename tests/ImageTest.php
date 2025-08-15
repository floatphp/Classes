<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Filesystem Component Tests
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file is a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Tests\Classes\Filesystem;

use PHPUnit\Framework\TestCase;
use FloatPHP\Classes\Filesystem\Image;
use FloatPHP\Classes\Filesystem\File;

/**
 * Image class tests.
 */
final class ImageTest extends TestCase
{
    private static string $testDir;
    private static string $testImage;
    private static string $outputDir;

    public static function setUpBeforeClass() : void
    {
        self::$testDir = sys_get_temp_dir() . '/floatphp_image_test_' . uniqid();
        self::$outputDir = self::$testDir . '/output';
        self::$testImage = self::$testDir . '/test.png';

        mkdir(self::$testDir, 0777, true);
        mkdir(self::$outputDir, 0777, true);

        // Create a simple test image
        self::createTestImage();
    }

    public static function tearDownAfterClass() : void
    {
        self::cleanupTestFiles();
    }

    /**
     * Create a simple test image for testing.
     */
    private static function createTestImage() : void
    {
        if ( !extension_loaded('gd') ) {
            self::markTestSkipped('GD extension not available');
            return;
        }

        $image = imagecreatetruecolor(200, 150);
        $color = imagecolorallocate($image, 255, 0, 0); // Red
        imagefill($image, 0, 0, $color);
        imagepng($image, self::$testImage);
        imagedestroy($image);
    }

    /**
     * Clean up test files.
     */
    private static function cleanupTestFiles() : void
    {
        if ( is_dir(self::$testDir) ) {
            $files = scandir(self::$testDir);
            foreach ($files as $file) {
                if ( $file !== '.' && $file !== '..' ) {
                    $path = self::$testDir . '/' . $file;
                    if ( is_dir($path) ) {
                        $subFiles = scandir($path);
                        foreach ($subFiles as $subFile) {
                            if ( $subFile !== '.' && $subFile !== '..' ) {
                                unlink($path . '/' . $subFile);
                            }
                        }
                        rmdir($path);
                    } else {
                        unlink($path);
                    }
                }
            }
            rmdir(self::$testDir);
        }
    }

    /**
     * Test image MIME validation.
     */
    public function testIsMime() : void
    {
        if ( !extension_loaded('gd') ) {
            $this->markTestSkipped('GD extension not available');
        }

        $this->assertTrue(Image::isMime(self::$testImage));

        // Create a non-image file
        $textFile = self::$testDir . '/test.txt';
        file_put_contents($textFile, 'This is not an image');
        $this->assertFalse(Image::isMime($textFile));
    }

    /**
     * Test format support checking.
     */
    public function testIsFormatSupported() : void
    {
        $this->assertTrue(Image::isFormatSupported('image/jpeg'));
        $this->assertTrue(Image::isFormatSupported('image/png'));
        $this->assertTrue(Image::isFormatSupported('image/gif'));
        $this->assertFalse(Image::isFormatSupported('image/tiff'));
        $this->assertFalse(Image::isFormatSupported('text/plain'));
    }

    /**
     * Test getting supported formats.
     */
    public function testGetSupportedFormats() : void
    {
        $formats = Image::getSupportedFormats();

        $this->assertIsArray($formats);
        $this->assertContains('image/jpeg', $formats);
        $this->assertContains('image/png', $formats);
        $this->assertContains('image/gif', $formats);
    }

    /**
     * Test getting image information.
     */
    public function testGetInfo() : void
    {
        if ( !extension_loaded('gd') ) {
            $this->markTestSkipped('GD extension not available');
        }

        $info = Image::getInfo(self::$testImage);

        $this->assertIsArray($info);
        $this->assertEquals(200, $info['width']);
        $this->assertEquals(150, $info['height']);
        $this->assertEquals('image/png', $info['format']);
    }

    /**
     * Test basic image resizing.
     */
    public function testResize() : void
    {
        if ( !extension_loaded('gd') ) {
            $this->markTestSkipped('GD extension not available');
        }

        $output = self::$outputDir . '/resized.png';
        $result = Image::resize(self::$testImage, 100, 75, [
            'output' => $output
        ]);

        $this->assertTrue($result);
        $this->assertTrue(file_exists($output));

        $info = Image::getInfo($output);
        $this->assertEquals(100, $info['width']);
        $this->assertEquals(75, $info['height']);
    }

    /**
     * Test thumbnail creation.
     */
    public function testThumbnail() : void
    {
        if ( !extension_loaded('gd') ) {
            $this->markTestSkipped('GD extension not available');
        }

        $output = self::$outputDir . '/thumbnail.png';
        $result = Image::thumbnail(self::$testImage, 50, $output);

        $this->assertTrue($result);
        $this->assertTrue(file_exists($output));

        $info = Image::getInfo($output);
        $this->assertEquals(50, $info['width']);
        $this->assertEquals(50, $info['height']);
    }

    /**
     * Test image rotation.
     */
    public function testRotate() : void
    {
        if ( !extension_loaded('gd') || !function_exists('imagerotate') ) {
            $this->markTestSkipped('GD extension or imagerotate function not available');
        }

        $output = self::$outputDir . '/rotated.png';
        $result = Image::rotate(self::$testImage, 90, $output);

        $this->assertTrue($result);
        $this->assertTrue(file_exists($output));

        $info = Image::getInfo($output);
        // After 90-degree rotation, dimensions should be swapped
        $this->assertEquals(150, $info['width']);
        $this->assertEquals(200, $info['height']);
    }

    /**
     * Test image format conversion.
     */
    public function testConvert() : void
    {
        if ( !extension_loaded('gd') ) {
            $this->markTestSkipped('GD extension not available');
        }

        $output = self::$outputDir . '/converted.jpeg';
        $result = Image::convert(self::$testImage, 'jpeg', $output);

        $this->assertTrue($result);
        $this->assertTrue(file_exists($output));

        $info = Image::getInfo($output);
        $this->assertEquals('image/jpeg', $info['format']);
    }

    /**
     * Test invalid path handling.
     */
    public function testInvalidPath() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        Image::resize('/nonexistent/path/image.jpg', 100, 100);
    }

    /**
     * Test invalid dimensions.
     */
    public function testInvalidDimensions() : void
    {
        if ( !extension_loaded('gd') ) {
            $this->markTestSkipped('GD extension not available');
        }

        $this->expectException(\InvalidArgumentException::class);
        Image::resize(self::$testImage, 0, 100);
    }

    /**
     * Test dimensions too large.
     */
    public function testDimensionsTooLarge() : void
    {
        if ( !extension_loaded('gd') ) {
            $this->markTestSkipped('GD extension not available');
        }

        $this->expectException(\InvalidArgumentException::class);
        Image::resize(self::$testImage, 20000, 20000);
    }

    /**
     * Test unsupported format conversion.
     */
    public function testUnsupportedFormatConversion() : void
    {
        if ( !extension_loaded('gd') ) {
            $this->markTestSkipped('GD extension not available');
        }

        $this->expectException(\InvalidArgumentException::class);
        Image::convert(self::$testImage, 'tiff');
    }

    /**
     * Test resize with crop option.
     */
    public function testResizeWithCrop() : void
    {
        if ( !extension_loaded('gd') ) {
            $this->markTestSkipped('GD extension not available');
        }

        $output = self::$outputDir . '/cropped.png';
        $result = Image::resize(self::$testImage, 100, 100, [
            'crop'   => true,
            'output' => $output
        ]);

        $this->assertTrue($result);
        $this->assertTrue(file_exists($output));

        $info = Image::getInfo($output);
        $this->assertEquals(100, $info['width']);
        $this->assertEquals(100, $info['height']);
    }

    /**
     * Test resize with maintain aspect ratio.
     */
    public function testResizeWithAspectRatio() : void
    {
        if ( !extension_loaded('gd') ) {
            $this->markTestSkipped('GD extension not available');
        }

        $output = self::$outputDir . '/aspect.png';
        $result = Image::resize(self::$testImage, 100, 100, [
            'maintain_aspect' => true,
            'output'          => $output
        ]);

        $this->assertTrue($result);
        $this->assertTrue(file_exists($output));

        $info = Image::getInfo($output);
        // Should maintain aspect ratio, so not exactly 100x100
        $this->assertTrue($info['width'] <= 100);
        $this->assertTrue($info['height'] <= 100);
    }

    /**
     * Test resize without upscaling.
     */
    public function testResizeWithoutUpscaling() : void
    {
        if ( !extension_loaded('gd') ) {
            $this->markTestSkipped('GD extension not available');
        }

        $output = self::$outputDir . '/no_upscale.png';

        // Try to resize to larger dimensions with upscale disabled
        $result = Image::resize(self::$testImage, 400, 300, [
            'upscale' => false,
            'output'  => $output
        ]);

        $this->assertTrue($result);
        $this->assertTrue(file_exists($output));

        $info = Image::getInfo($output);
        // Should keep original dimensions since upscaling is disabled
        $this->assertEquals(200, $info['width']);
        $this->assertEquals(150, $info['height']);
    }
}
