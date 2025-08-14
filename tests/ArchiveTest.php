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
use FloatPHP\Classes\Filesystem\Archive;
use \ZipArchive;

/**
 * Unit tests for Archive class.
 */
class ArchiveTest extends TestCase
{
    /**
     * @var string
     */
    private $testDir;

    /**
     * @var string
     */
    private $testFile;

    /**
     * @var string
     */
    private $testArchive;

    /**
     * Set up test environment before each test.
     */
    protected function setUp() : void
    {
        $this->testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'floatphp_archive_test_' . uniqid();
        $this->testFile = $this->testDir . DIRECTORY_SEPARATOR . 'test.txt';
        $this->testArchive = $this->testDir . DIRECTORY_SEPARATOR . 'test.zip';

        // Create test directory and file
        if ( !is_dir($this->testDir) ) {
            mkdir($this->testDir, 0755, true);
        }
        file_put_contents($this->testFile, 'Hello World Test Content');
    }

    /**
     * Test compress method with file.
     */
    public function testCompressFile() : void
    {
        if ( !extension_loaded('zip') ) {
            $this->markTestSkipped('ZIP extension is not available.');
        }

        $result = Archive::compress($this->testFile, $this->testDir, 'test_file');
        $this->assertTrue($result);
        $this->assertFileExists($this->testDir . DIRECTORY_SEPARATOR . 'test_file.zip');
    }

    /**
     * Test compress method with directory.
     */
    public function testCompressDirectory() : void
    {
        if ( !extension_loaded('zip') ) {
            $this->markTestSkipped('ZIP extension is not available.');
        }

        // Create subdirectory with files
        $subDir = $this->testDir . DIRECTORY_SEPARATOR . 'subdir';
        mkdir($subDir);
        file_put_contents($subDir . DIRECTORY_SEPARATOR . 'sub_test.txt', 'Sub content');

        $result = Archive::compress($this->testDir, dirname($this->testDir), 'test_dir');
        $this->assertTrue($result);

        $archivePath = dirname($this->testDir) . DIRECTORY_SEPARATOR . 'test_dir.zip';
        $this->assertFileExists($archivePath);

        // Clean up
        if ( file_exists($archivePath) ) {
            unlink($archivePath);
        }
    }

    /**
     * Test compress method with non-existent path.
     */
    public function testCompressNonExistentPath() : void
    {
        $result = Archive::compress('/non/existent/path');
        $this->assertFalse($result);
    }

    /**
     * Test uncompress method.
     */
    public function testUncompress() : void
    {
        if ( !extension_loaded('zip') ) {
            $this->markTestSkipped('ZIP extension is not available.');
        }

        // First create an archive
        $zip = new ZipArchive();
        if ( $zip->open($this->testArchive, ZipArchive::CREATE) === true ) {
            $zip->addFile($this->testFile, 'test.txt');
            $zip->close();

            $extractDir = $this->testDir . DIRECTORY_SEPARATOR . 'extracted';
            mkdir($extractDir);

            $result = Archive::uncompress($this->testArchive, $extractDir);
            $this->assertTrue($result);
            $this->assertFileExists($extractDir . DIRECTORY_SEPARATOR . 'test.txt');
        } else {
            $this->markTestSkipped('Could not create test archive.');
        }
    }

    /**
     * Test uncompress with remove option.
     */
    public function testUncompressWithRemove() : void
    {
        if ( !extension_loaded('zip') ) {
            $this->markTestSkipped('ZIP extension is not available.');
        }

        // Create a test archive
        $zip = new ZipArchive();
        if ( $zip->open($this->testArchive, ZipArchive::CREATE) === true ) {
            $zip->addFile($this->testFile, 'test.txt');
            $zip->close();

            $extractDir = $this->testDir . DIRECTORY_SEPARATOR . 'extracted2';
            mkdir($extractDir);

            $result = Archive::uncompress($this->testArchive, $extractDir, true);
            $this->assertTrue($result);
            $this->assertFileExists($extractDir . DIRECTORY_SEPARATOR . 'test.txt');
            $this->assertFileDoesNotExist($this->testArchive);
        } else {
            $this->markTestSkipped('Could not create test archive.');
        }
    }

    /**
     * Test uncompress with non-existent archive.
     */
    public function testUncompressNonExistentArchive() : void
    {
        $result = Archive::uncompress('/non/existent/archive.zip');
        $this->assertFalse($result);
    }

    /**
     * Test isValid method with valid archive.
     */
    public function testIsValidWithValidArchive() : void
    {
        if ( !extension_loaded('zip') ) {
            $this->markTestSkipped('ZIP extension is not available.');
        }

        // Create a valid archive
        $zip = new ZipArchive();
        if ( $zip->open($this->testArchive, ZipArchive::CREATE) === true ) {
            $zip->addFile($this->testFile, 'test.txt');
            $zip->close();

            $result = Archive::isValid($this->testArchive);
            $this->assertTrue($result);
        } else {
            $this->markTestSkipped('Could not create test archive.');
        }
    }

    /**
     * Test isValid method with invalid archive.
     */
    public function testIsValidWithInvalidArchive() : void
    {
        // Create a fake zip file
        file_put_contents($this->testArchive, 'This is not a valid zip file');

        $result = Archive::isValid($this->testArchive);
        $this->assertFalse($result);
    }

    /**
     * Test isValid method with non-existent file.
     */
    public function testIsValidWithNonExistentFile() : void
    {
        $result = Archive::isValid('/non/existent/file.zip');
        $this->assertFalse($result);
    }

    /**
     * Test getInfo method.
     */
    public function testGetInfo() : void
    {
        if ( !extension_loaded('zip') ) {
            $this->markTestSkipped('ZIP extension is not available.');
        }

        // Create a test archive with multiple files
        $zip = new ZipArchive();
        if ( $zip->open($this->testArchive, ZipArchive::CREATE) === true ) {
            $zip->addFile($this->testFile, 'test.txt');
            $zip->addFromString('another.txt', 'Another file content');
            $zip->setArchiveComment('Test archive comment');
            $zip->close();

            $info = Archive::getInfo($this->testArchive);

            $this->assertIsArray($info);
            $this->assertEquals('test.zip', $info['filename']);
            $this->assertEquals(2, $info['numFiles']);
            $this->assertEquals('Test archive comment', $info['comment']);
            $this->assertCount(2, $info['files']);

            // Check file details
            $this->assertEquals('test.txt', $info['files'][0]['name']);
            $this->assertEquals('another.txt', $info['files'][1]['name']);
        } else {
            $this->markTestSkipped('Could not create test archive.');
        }
    }

    /**
     * Test getInfo method with invalid archive.
     */
    public function testGetInfoWithInvalidArchive() : void
    {
        $result = Archive::getInfo('/non/existent/file.zip');
        $this->assertFalse($result);
    }

    /**
     * Test extractFile method.
     */
    public function testExtractFile() : void
    {
        if ( !extension_loaded('zip') ) {
            $this->markTestSkipped('ZIP extension is not available.');
        }

        // Create a test archive
        $zip = new ZipArchive();
        if ( $zip->open($this->testArchive, ZipArchive::CREATE) === true ) {
            $zip->addFile($this->testFile, 'test.txt');
            $zip->addFromString('other.txt', 'Other content');
            $zip->close();

            $extractDir = $this->testDir . DIRECTORY_SEPARATOR . 'single_extract';
            mkdir($extractDir);

            $result = Archive::extractFile($this->testArchive, 'test.txt', $extractDir);
            $this->assertTrue($result);
            $this->assertFileExists($extractDir . DIRECTORY_SEPARATOR . 'test.txt');
            $this->assertFileDoesNotExist($extractDir . DIRECTORY_SEPARATOR . 'other.txt');
        } else {
            $this->markTestSkipped('Could not create test archive.');
        }
    }

    /**
     * Test extractFile method with non-existent file.
     */
    public function testExtractFileWithNonExistentFile() : void
    {
        if ( !extension_loaded('zip') ) {
            $this->markTestSkipped('ZIP extension is not available.');
        }

        // Create a test archive
        $zip = new ZipArchive();
        if ( $zip->open($this->testArchive, ZipArchive::CREATE) === true ) {
            $zip->addFile($this->testFile, 'test.txt');
            $zip->close();

            $result = Archive::extractFile($this->testArchive, 'non_existent.txt', $this->testDir);
            $this->assertFalse($result);
        } else {
            $this->markTestSkipped('Could not create test archive.');
        }
    }

    /**
     * Test isGzip method with valid gzip file.
     */
    public function testIsGzipWithValidFile() : void
    {
        if ( !extension_loaded('zlib') ) {
            $this->markTestSkipped('ZLIB extension is not available.');
        }

        $gzipFile = $this->testDir . DIRECTORY_SEPARATOR . 'test.txt.gz';
        $gz = gzopen($gzipFile, 'w9');
        gzwrite($gz, 'Test gzip content');
        gzclose($gz);

        $result = Archive::isGzip($gzipFile);
        $this->assertTrue($result);
    }

    /**
     * Test isGzip method with invalid file.
     */
    public function testIsGzipWithInvalidFile() : void
    {
        $fakeGzipFile = $this->testDir . DIRECTORY_SEPARATOR . 'fake.gz';
        file_put_contents($fakeGzipFile, 'This is not a gzip file');

        $result = Archive::isGzip($fakeGzipFile);
        $this->assertFalse($result);
    }

    /**
     * Test isGzip method with non-gz extension.
     */
    public function testIsGzipWithNonGzExtension() : void
    {
        $result = Archive::isGzip($this->testFile);
        $this->assertFalse($result);
    }

    /**
     * Test unGzip method.
     */
    public function testUnGzip() : void
    {
        if ( !extension_loaded('zlib') ) {
            $this->markTestSkipped('ZLIB extension is not available.');
        }

        $gzipFile = $this->testDir . DIRECTORY_SEPARATOR . 'test.txt.gz';
        $content = 'Test gzip content for extraction';

        $gz = gzopen($gzipFile, 'w9');
        gzwrite($gz, $content);
        gzclose($gz);

        $result = Archive::unGzip($gzipFile);
        $this->assertTrue($result);

        $extractedFile = $this->testDir . DIRECTORY_SEPARATOR . 'test.txt';
        $this->assertFileExists($extractedFile);
        $this->assertEquals($content, file_get_contents($extractedFile));
    }

    /**
     * Test unGzip method with remove option.
     */
    public function testUnGzipWithRemove() : void
    {
        if ( !extension_loaded('zlib') ) {
            $this->markTestSkipped('ZLIB extension is not available.');
        }

        $gzipFile = $this->testDir . DIRECTORY_SEPARATOR . 'test2.txt.gz';
        $content = 'Test gzip content for extraction with remove';

        $gz = gzopen($gzipFile, 'w9');
        gzwrite($gz, $content);
        gzclose($gz);

        $result = Archive::unGzip($gzipFile, 4096, true);
        $this->assertTrue($result);

        $extractedFile = $this->testDir . DIRECTORY_SEPARATOR . 'test2.txt';
        $this->assertFileExists($extractedFile);
        $this->assertFileDoesNotExist($gzipFile);
        $this->assertEquals($content, file_get_contents($extractedFile));
    }

    /**
     * Test unGzip method with non-existent file.
     */
    public function testUnGzipWithNonExistentFile() : void
    {
        $result = Archive::unGzip('/non/existent/file.gz');
        $this->assertFalse($result);
    }

    /**
     * Clean up after each test.
     */
    protected function tearDown() : void
    {
        // Recursively remove test directory
        if ( is_dir($this->testDir) ) {
            $this->removeDirectory($this->testDir);
        }
    }

    /**
     * Helper method to recursively remove directory.
     */
    private function removeDirectory(string $dir) : void
    {
        if ( !is_dir($dir) ) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if ( is_dir($path) ) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
