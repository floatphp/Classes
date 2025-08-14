<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component Tests
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Tests\Classes\Http;

use PHPUnit\Framework\TestCase;
use FloatPHP\Classes\Http\Post;

/**
 * Post class tests.
 */
final class PostTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear POST data before each test
        $_POST = [];
    }

    protected function tearDown(): void
    {
        // Clear POST data after each test
        $_POST = [];
    }

    /**
     * Test get POST value.
     */
    public function testGetValue(): void
    {
        $_POST['test_key'] = 'test_value';
        
        $value = Post::get('test_key');
        $this->assertEquals('test_value', $value);
    }

    /**
     * Test get all POST values.
     */
    public function testGetAllValues(): void
    {
        $_POST['key1'] = 'value1';
        $_POST['key2'] = 'value2';
        
        $values = Post::get();
        $this->assertIsArray($values);
        $this->assertEquals('value1', $values['key1']);
        $this->assertEquals('value2', $values['key2']);
    }

    /**
     * Test get non-existent POST value.
     */
    public function testGetNonExistentValue(): void
    {
        $value = Post::get('non_existent');
        $this->assertNull($value);
    }

    /**
     * Test get when no POST values exist.
     */
    public function testGetWhenNoValuesExist(): void
    {
        $values = Post::get();
        $this->assertNull($values);
    }

    /**
     * Test set POST value.
     */
    public function testSetValue(): void
    {
        Post::set('new_key', 'new_value');
        
        $this->assertEquals('new_value', $_POST['new_key']);
    }

    /**
     * Test set POST value with null.
     */
    public function testSetValueWithNull(): void
    {
        Post::set('null_key');
        
        $this->assertNull($_POST['null_key']);
    }

    /**
     * Test set POST value with different types.
     */
    public function testSetValueWithDifferentTypes(): void
    {
        Post::set('string_key', 'string_value');
        Post::set('int_key', 123);
        Post::set('bool_key', true);
        Post::set('array_key', ['nested' => 'value']);
        
        $this->assertEquals('string_value', $_POST['string_key']);
        $this->assertEquals(123, $_POST['int_key']);
        $this->assertTrue($_POST['bool_key']);
        $this->assertIsArray($_POST['array_key']);
    }

    /**
     * Test check if POST value is set.
     */
    public function testIsSetted(): void
    {
        $_POST['test_key'] = 'test_value';
        
        $this->assertTrue(Post::isSetted('test_key'));
        $this->assertFalse(Post::isSetted('non_existent'));
    }

    /**
     * Test check if any POST values are set.
     */
    public function testIsSettedAny(): void
    {
        $this->assertFalse(Post::isSetted());
        
        $_POST['test_key'] = 'test_value';
        $this->assertTrue(Post::isSetted());
    }

    /**
     * Test unset specific POST value.
     */
    public function testUnsetSpecificValue(): void
    {
        $_POST['key1'] = 'value1';
        $_POST['key2'] = 'value2';
        
        Post::unset('key1');
        
        $this->assertFalse(isset($_POST['key1']));
        $this->assertTrue(isset($_POST['key2']));
    }

    /**
     * Test unset all POST values.
     */
    public function testUnsetAllValues(): void
    {
        $_POST['key1'] = 'value1';
        $_POST['key2'] = 'value2';
        
        Post::unset();
        
        $this->assertEmpty($_POST);
    }

    /**
     * Test POST value with empty string.
     */
    public function testGetValueWithEmptyString(): void
    {
        $_POST['empty_key'] = '';
        
        $value = Post::get('empty_key');
        $this->assertEquals('', $value);
        $this->assertTrue(Post::isSetted('empty_key'));
    }

    /**
     * Test POST value with zero.
     */
    public function testGetValueWithZero(): void
    {
        $_POST['zero_key'] = 0;
        
        $value = Post::get('zero_key');
        $this->assertEquals(0, $value);
        $this->assertTrue(Post::isSetted('zero_key'));
    }

    /**
     * Test POST value with false.
     */
    public function testGetValueWithFalse(): void
    {
        $_POST['false_key'] = false;
        
        $value = Post::get('false_key');
        $this->assertFalse($value);
        $this->assertTrue(Post::isSetted('false_key'));
    }

    /**
     * Test overwrite existing POST value.
     */
    public function testOverwriteExistingValue(): void
    {
        $_POST['existing_key'] = 'old_value';
        Post::set('existing_key', 'new_value');
        
        $this->assertEquals('new_value', $_POST['existing_key']);
    }

    /**
     * Test complex array structure.
     */
    public function testComplexArrayStructure(): void
    {
        $complex = [
            'user' => [
                'profile' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com'
                ]
            ]
        ];
        
        Post::set('user_data', $complex);
        $retrieved = Post::get('user_data');
        
        $this->assertEquals('John Doe', $retrieved['user']['profile']['name']);
        $this->assertEquals('john@example.com', $retrieved['user']['profile']['email']);
    }

    /**
     * Test form submission simulation.
     */
    public function testFormSubmissionSimulation(): void
    {
        // Simulate form data
        $_POST['username'] = 'testuser';
        $_POST['password'] = 'testpass';
        $_POST['email'] = 'test@example.com';
        $_POST['remember'] = true;
        
        $this->assertEquals('testuser', Post::get('username'));
        $this->assertEquals('testpass', Post::get('password'));
        $this->assertEquals('test@example.com', Post::get('email'));
        $this->assertTrue(Post::get('remember'));
        $this->assertTrue(Post::isSetted());
    }

    /**
     * Test file upload simulation.
     */
    public function testFileUploadSimulation(): void
    {
        $fileData = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'size' => 12345,
            'tmp_name' => '/tmp/phpXXXXXX',
            'error' => 0
        ];
        
        Post::set('uploaded_file', $fileData);
        $retrieved = Post::get('uploaded_file');
        
        $this->assertEquals('test.jpg', $retrieved['name']);
        $this->assertEquals('image/jpeg', $retrieved['type']);
        $this->assertEquals(12345, $retrieved['size']);
    }

    /**
     * Test large POST data.
     */
    public function testLargePostData(): void
    {
        $largeData = str_repeat('Lorem ipsum dolor sit amet. ', 1000);
        Post::set('large_text', $largeData);
        
        $retrieved = Post::get('large_text');
        $this->assertEquals($largeData, $retrieved);
        $this->assertGreaterThan(25000, strlen($retrieved));
    }
}
