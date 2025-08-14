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
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Tests\Classes\Filesystem;

use PHPUnit\Framework\TestCase;
use FloatPHP\Classes\Filesystem\File;

/**
 * File class tests.
 */
final class FileTest extends TestCase
{
    private static string $testDir;
    private static string $testFile;

    public static function setUpBeforeClass(): void
    {
        self::$testDir = sys_get_temp_dir() . '/floatphp_test_' . uniqid();
        self::$testFile = self::$testDir . '/test.txt';
        
        mkdir(self::$testDir, 0777, true);
        file_put_contents(self::$testFile, "Line 1\nLine 2\nLine 3");
    }

    public static function tearDownAfterClass(): void
    {
        if (file_exists(self::$testFile)) {
            unlink(self::$testFile);
        }
        if (is_dir(self::$testDir)) {
            rmdir(self::$testDir);
        }
    }

    /**
     * Test file analysis.
     */
    public function testAnalyse(): void
    {
        $analysis = File::analyse(self::$testFile);
        
        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('parent', $analysis);
        $this->assertArrayHasKey('name', $analysis);
        $this->assertArrayHasKey('filename', $analysis);
        $this->assertArrayHasKey('extension', $analysis);
        $this->assertArrayHasKey('size', $analysis);
        $this->assertArrayHasKey('type', $analysis);
    }

    /**
     * Test getting parent directory.
     */
    public function testGetParentDir(): void
    {
        $parent = File::getParentDir(self::$testFile);
        // Normalize path for comparison
        $expectedParent = str_replace('\\', '/', self::$testDir);
        $this->assertEquals($expectedParent, $parent);
    }

    /**
     * Test getting file extension.
     */
    public function testGetExtension(): void
    {
        $ext = File::getExtension(self::$testFile);
        $this->assertEquals('txt', $ext);
        
        $extUnformatted = File::getExtension(self::$testFile, false);
        $this->assertEquals('txt', $extUnformatted);
    }

    /**
     * Test getting file name without extension.
     */
    public function testGetName(): void
    {
        $name = File::getName(self::$testFile);
        $this->assertEquals('test', $name);
    }

    /**
     * Test getting full filename.
     */
    public function testGetFileName(): void
    {
        $filename = File::getFileName(self::$testFile);
        $this->assertEquals('test.txt', $filename);
    }

    /**
     * Test getting file size.
     */
    public function testGetSize(): void
    {
        $size = File::getSize(self::$testFile);
        $this->assertIsString($size);
        $this->assertStringContainsString('B', $size);
        
        $sizeWithDecimals = File::getSize(self::$testFile, 3);
        $this->assertIsString($sizeWithDecimals);
    }

    /**
     * Test getting file permissions.
     */
    public function testGetPermissions(): void
    {
        $permissions = File::getPermissions(self::$testFile);
        $this->assertIsString($permissions);
        $this->assertEquals(4, strlen($permissions));
        
        $permissionsInt = File::getPermissions(self::$testFile, true);
        $this->assertIsInt($permissionsInt);
    }

    /**
     * Test getting file lines.
     */
    public function testGetLines(): void
    {
        $lines = File::getLines(self::$testFile);
        $this->assertIsArray($lines);
        $this->assertCount(3, $lines);
        $this->assertStringContainsString('Line 1', $lines[0]);
    }

    /**
     * Test checking if path is file.
     */
    public function testIsFile(): void
    {
        $this->assertTrue(File::isFile(self::$testFile));
        $this->assertFalse(File::isFile(self::$testDir));
        $this->assertFalse(File::isFile('/nonexistent/file.txt'));
    }

    /**
     * Test checking if path is directory.
     */
    public function testIsDir(): void
    {
        $this->assertTrue(File::isDir(self::$testDir));
        $this->assertFalse(File::isDir(self::$testFile));
        $this->assertFalse(File::isDir('/nonexistent/directory'));
    }

    /**
     * Test file existence.
     */
    public function testExists(): void
    {
        $this->assertTrue(File::exists(self::$testFile));
        $this->assertTrue(File::exists(self::$testDir));
        $this->assertFalse(File::exists('/nonexistent/path'));
    }

    /**
     * Test checking if file is empty.
     */
    public function testIsEmpty(): void
    {
        $this->assertFalse(File::isEmpty(self::$testFile));
        
        // Create empty file
        $emptyFile = self::$testDir . '/empty.txt';
        touch($emptyFile);
        $this->assertTrue(File::isEmpty($emptyFile));
        unlink($emptyFile);
    }

    /**
     * Test file reading.
     */
    public function testRead(): void
    {
        $content = File::read(self::$testFile);
        $this->assertIsString($content);
        $this->assertStringContainsString('Line 1', $content);
        $this->assertStringContainsString('Line 2', $content);
    }

    /**
     * Test file writing.
     */
    public function testWrite(): void
    {
        $writeFile = self::$testDir . '/write_test.txt';
        $content = 'Test write content';
        
        $result = File::w($writeFile, $content);
        $this->assertTrue($result);
        $this->assertTrue(file_exists($writeFile));
        $this->assertEquals($content, file_get_contents($writeFile));
        
        unlink($writeFile);
    }

    /**
     * Test file copying.
     */
    public function testCopy(): void
    {
        $copyFile = self::$testDir . '/copy_test.txt';
        
        $result = File::copy(self::$testFile, $copyFile);
        $this->assertTrue($result);
        $this->assertTrue(file_exists($copyFile));
        $this->assertEquals(file_get_contents(self::$testFile), file_get_contents($copyFile));
        
        unlink($copyFile);
    }

    /**
     * Test file moving.
     */
    public function testMove(): void
    {
        $tempFile = self::$testDir . '/temp_move.txt';
        file_put_contents($tempFile, 'temp content');
        
        $moveFile = self::$testDir . '/moved_test.txt';
        
        $result = File::move($tempFile, $moveFile);
        $this->assertTrue($result);
        $this->assertFalse(file_exists($tempFile));
        $this->assertTrue(file_exists($moveFile));
        
        unlink($moveFile);
    }

    /**
     * Test file deletion.
     */
    public function testDelete(): void
    {
        $deleteFile = self::$testDir . '/delete_test.txt';
        file_put_contents($deleteFile, 'delete me');
        
        $this->assertTrue(file_exists($deleteFile));
        
        $result = File::remove($deleteFile);
        $this->assertTrue($result);
        $this->assertFalse(file_exists($deleteFile));
    }

    /**
     * Test getting MIME type.
     */
    public function testGetMimeType(): void
    {
        $mimeType = File::getMimeType(self::$testFile);
        $this->assertIsString($mimeType);
        // MIME type detection may vary, just ensure it returns a string
    }

    /**
     * Test file search functionality.
     */
    public function testSearch(): void
    {
        $results = File::scanDir(self::$testDir);
        $this->assertIsArray($results);
        $this->assertContains('test.txt', $results);
    }

    /**
     * Test edge cases with non-existent files.
     */
    public function testEdgeCases(): void
    {
        $nonExistent = '/nonexistent/file.txt';
        
        $this->assertFalse(File::isFile($nonExistent));
        $this->assertFalse(File::exists($nonExistent));
        $this->assertEquals('', File::read($nonExistent));
        $this->assertFalse(File::copy($nonExistent, '/tmp/test.txt'));
    }

    /**
     * Test path formatting.
     */
    public function testPathFormatting(): void
    {
        $windowsPath = 'C:\\test\\file.txt';
        $unixPath = \FloatPHP\Classes\Filesystem\Stringify::formatPath($windowsPath);
        $this->assertStringNotContainsString('\\', $unixPath);
    }
}
