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
use FloatPHP\Classes\Filesystem\Arrayify;

/**
 * Unit tests for Arrayify class.
 */
class ArrayifyTest extends TestCase
{
    /**
     * Test inArray method with strict comparison.
     */
    public function testInArray() : void
    {
        $array = [1, 2, 3, 'hello', 'world'];
        
        $this->assertTrue(Arrayify::inArray(1, $array));
        $this->assertTrue(Arrayify::inArray('hello', $array));
        $this->assertFalse(Arrayify::inArray('1', $array)); // Strict comparison
        $this->assertFalse(Arrayify::inArray(4, $array));
    }

    /**
     * Test search method with strict comparison.
     */
    public function testSearch() : void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        
        $this->assertEquals('a', Arrayify::search(1, $array));
        $this->assertEquals('c', Arrayify::search(3, $array));
        $this->assertFalse(Arrayify::search('1', $array)); // Strict comparison
        $this->assertFalse(Arrayify::search(4, $array));
    }

    /**
     * Test merge method with multiple arrays.
     */
    public function testMerge() : void
    {
        $array1 = [1, 2, 3];
        $array2 = [4, 5];
        $array3 = [6, 7, 8];
        
        $result = Arrayify::merge($array1, $array2, $array3);
        $expected = [1, 2, 3, 4, 5, 6, 7, 8];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test merge method with associative arrays.
     */
    public function testMergeAssociative() : void
    {
        $array1 = ['a' => 1, 'b' => 2];
        $array2 = ['c' => 3, 'd' => 4];
        
        $result = Arrayify::merge($array1, $array2);
        $expected = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test mergeAll method with nested arrays.
     */
    public function testMergeAll() : void
    {
        $default = [
            'name' => 'default',
            'config' => [
                'debug' => true,
                'timeout' => 30
            ]
        ];
        
        $target = [
            'name' => 'custom',
            'config' => [
                'timeout' => 60
            ]
        ];
        
        $result = Arrayify::mergeAll($default, $target);
        
        $expected = [
            'name' => 'custom',
            'config' => [
                'debug' => true,
                'timeout' => 60
            ]
        ];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test mergeAll method with strict mode.
     */
    public function testMergeAllStrict() : void
    {
        $default = [
            'name' => 'default',
            'value' => 'fallback'
        ];
        
        $target = [
            'name' => '',
            'value' => 'existing'
        ];
        
        $result = Arrayify::mergeAll($default, $target, true);
        
        // In strict mode, empty string should be replaced
        $expected = [
            'name' => 'default',
            'value' => 'existing'
        ];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test push method.
     */
    public function testPush() : void
    {
        $array = [1, 2, 3];
        $count = Arrayify::push($array, 4, 5, 6);
        
        $this->assertEquals(6, $count);
        $this->assertEquals([1, 2, 3, 4, 5, 6], $array);
    }

    /**
     * Test combine method.
     */
    public function testCombine() : void
    {
        $keys = ['name', 'age', 'city'];
        $values = ['John', 30, 'New York'];
        
        $result = Arrayify::combine($keys, $values);
        $expected = ['name' => 'John', 'age' => 30, 'city' => 'New York'];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test map method.
     */
    public function testMap() : void
    {
        $array = [1, 2, 3, 4];
        $result = Arrayify::map('strtoupper', ['a', 'b', 'c', 'd']);
        
        $this->assertEquals(['A', 'B', 'C', 'D'], $result);
        
        // Test with callback
        $result = Arrayify::map(function($x) { return $x * 2; }, $array);
        $this->assertEquals([2, 4, 6, 8], $result);
    }

    /**
     * Test shift method.
     */
    public function testShift() : void
    {
        $array = ['first', 'second', 'third'];
        $shifted = Arrayify::shift($array);
        
        $this->assertEquals('first', $shifted);
        $this->assertEquals(['second', 'third'], $array);
    }

    /**
     * Test pop method.
     */
    public function testPop() : void
    {
        $array = ['first', 'second', 'third'];
        $popped = Arrayify::pop($array);
        
        $this->assertEquals('third', $popped);
        $this->assertEquals(['first', 'second'], $array);
    }

    /**
     * Test diff method.
     */
    public function testDiff() : void
    {
        $array1 = [1, 2, 3, 4, 5];
        $array2 = [3, 4];
        $array3 = [5, 6];
        
        $result = Arrayify::diff($array1, $array2, $array3);
        $expected = [0 => 1, 1 => 2]; // Keys are preserved
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test hasKey method.
     */
    public function testHasKey() : void
    {
        $array = ['name' => 'John', 'age' => 30, 0 => 'first'];
        
        $this->assertTrue(Arrayify::hasKey('name', $array));
        $this->assertTrue(Arrayify::hasKey(0, $array));
        $this->assertFalse(Arrayify::hasKey('city', $array));
        $this->assertFalse(Arrayify::hasKey(1, $array));
    }

    /**
     * Test keys method.
     */
    public function testKeys() : void
    {
        $array = ['name' => 'John', 'age' => 30, 'city' => 'New York'];
        
        $result = Arrayify::keys($array);
        $expected = ['name', 'age', 'city'];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test keys method with search.
     */
    public function testKeysWithSearch() : void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 1, 'd' => 3];
        
        $result = Arrayify::keys($array, 1, true);
        $expected = ['a', 'c'];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test key method (first key).
     */
    public function testKey() : void
    {
        $array = ['name' => 'John', 'age' => 30];
        $result = Arrayify::key($array);
        
        $this->assertEquals('name', $result);
        
        // Test with empty array
        $empty = [];
        $this->assertNull(Arrayify::key($empty));
    }

    /**
     * Test fillKeys method.
     */
    public function testFillKeys() : void
    {
        $keys = ['name', 'age', 'city'];
        $result = Arrayify::fillKeys($keys, 'default');
        
        $expected = ['name' => 'default', 'age' => 'default', 'city' => 'default'];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test values method.
     */
    public function testValues() : void
    {
        $array = ['name' => 'John', 'age' => 30, 'city' => 'New York'];
        $result = Arrayify::values($array);
        
        $expected = ['John', 30, 'New York'];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test rand method.
     */
    public function testRand() : void
    {
        $array = ['a', 'b', 'c', 'd', 'e'];
        
        // Test single random key
        $result = Arrayify::rand($array);
        $this->assertIsInt($result);
        $this->assertArrayHasKey($result, $array);
        
        // Test multiple random keys
        $result = Arrayify::rand($array, 3);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        
        foreach ($result as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }

    /**
     * Test slice method.
     */
    public function testSlice() : void
    {
        $array = ['a', 'b', 'c', 'd', 'e'];
        
        $result = Arrayify::slice($array, 1, 3);
        $expected = ['b', 'c', 'd'];
        
        $this->assertEquals($expected, $result);
        
        // Test with negative offset
        $result = Arrayify::slice($array, -2);
        $expected = ['d', 'e'];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test slice method with key preservation.
     */
    public function testSlicePreserveKeys() : void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
        
        $result = Arrayify::slice($array, 1, 2, true);
        $expected = ['b' => 2, 'c' => 3];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test chunk method.
     */
    public function testChunk() : void
    {
        $array = [1, 2, 3, 4, 5, 6, 7];
        
        $result = Arrayify::chunk($array, 3);
        $expected = [[1, 2, 3], [4, 5, 6], [7]];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test chunk method with key preservation.
     */
    public function testChunkPreserveKeys() : void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
        
        $result = Arrayify::chunk($array, 2, true);
        $expected = [['a' => 1, 'b' => 2], ['c' => 3, 'd' => 4]];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test filter method.
     */
    public function testFilter() : void
    {
        $array = [1, 2, 0, 3, '', 4, null, 5];
        
        // Default filter (removes falsy values)
        $result = Arrayify::filter($array);
        $expected = [0 => 1, 1 => 2, 3 => 3, 5 => 4, 7 => 5];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test filter method with callback.
     */
    public function testFilterWithCallback() : void
    {
        $array = [1, 2, 3, 4, 5, 6];
        
        $result = Arrayify::filter($array, function($value) {
            return $value % 2 === 0; // Even numbers only
        });
        
        $expected = [1 => 2, 3 => 4, 5 => 6];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test format method.
     */
    public function testFormat() : void
    {
        $array = ['a' => 1, 'b' => 0, 'c' => 2, 'd' => '', 'e' => 3];
        
        $result = Arrayify::format($array);
        // format() calls values() first (which reindexes), then filter() (which preserves keys)
        // So values are: [1, 0, 2, '', 3] at indices [0, 1, 2, 3, 4]
        // After filtering: [0 => 1, 2 => 2, 4 => 3]
        $expected = [0 => 1, 2 => 2, 4 => 3]; 
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test formatKeyCase method.
     */
    public function testFormatKeyCase() : void
    {
        $array = ['NAME' => 'John', 'AGE' => 30, 'City' => 'New York'];
        
        $result = Arrayify::formatKeyCase($array, CASE_LOWER);
        $expected = ['name' => 'John', 'age' => 30, 'city' => 'New York'];
        
        $this->assertEquals($expected, $result);
        
        $result = Arrayify::formatKeyCase($array, CASE_UPPER);
        $expected = ['NAME' => 'John', 'AGE' => 30, 'CITY' => 'New York'];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test recursive method.
     */
    public function testRecursive() : void
    {
        $array = [
            'user1' => ['name' => 'john', 'age' => 30],
            'user2' => ['name' => 'jane', 'age' => 25]
        ];
        
        $result = Arrayify::recursive($array, function(&$value, $key) {
            if ($key === 'name') {
                $value = strtoupper($value);
            }
        });
        
        $this->assertTrue($result);
        $this->assertEquals('JOHN', $array['user1']['name']);
        $this->assertEquals('JANE', $array['user2']['name']);
        $this->assertEquals(30, $array['user1']['age']); // Unchanged
    }

    /**
     * Test unique method.
     */
    public function testUnique() : void
    {
        $array = [1, 2, 2, 3, 3, 4, 1];
        
        $result = Arrayify::unique($array);
        $expected = [0 => 1, 1 => 2, 3 => 3, 5 => 4];
        
        $this->assertEquals($expected, $result);
    }

    /**
     * Test uniqueMultiple method.
     */
    public function testUniqueMultiple() : void
    {
        $array = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
            ['name' => 'John', 'age' => 30], // Duplicate
            ['name' => 'Bob', 'age' => 35]
        ];
        
        $result = Arrayify::uniqueMultiple($array);
        
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        
        // Check that we have the unique items (order may vary)
        $this->assertContains(['name' => 'John', 'age' => 30], $result);
        $this->assertContains(['name' => 'Jane', 'age' => 25], $result);
        $this->assertContains(['name' => 'Bob', 'age' => 35], $result);
    }

    /**
     * Test sort method.
     */
    public function testSort() : void
    {
        $array = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
            ['name' => 'Bob', 'age' => 35]
        ];
        
        $result = Arrayify::sort($array, 'age');
        
        $this->assertEquals('Jane', $result[0]['name']); // 25
        $this->assertEquals('John', $result[1]['name']); // 30
        $this->assertEquals('Bob', $result[2]['name']);  // 35
    }

    /**
     * Test sort method with DESC order.
     */
    public function testSortDesc() : void
    {
        $array = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
            ['name' => 'Bob', 'age' => 35]
        ];
        
        $result = Arrayify::sort($array, 'age', 'DESC');
        
        $this->assertEquals('Bob', $result[0]['name']);  // 35
        $this->assertEquals('John', $result[1]['name']); // 30
        $this->assertEquals('Jane', $result[2]['name']); // 25
    }

    /**
     * Test sort method with multiple fields.
     */
    public function testSortMultiple() : void
    {
        $array = [
            ['name' => 'John', 'age' => 30, 'score' => 85],
            ['name' => 'Jane', 'age' => 30, 'score' => 90],
            ['name' => 'Bob', 'age' => 25, 'score' => 80]
        ];
        
        $result = Arrayify::sort($array, ['age' => 'ASC', 'score' => 'DESC']);
        
        $this->assertEquals('Bob', $result[0]['name']);  // age: 25
        $this->assertEquals('Jane', $result[1]['name']); // age: 30, score: 90
        $this->assertEquals('John', $result[2]['name']); // age: 30, score: 85
    }

    /**
     * Test sort method with key preservation.
     */
    public function testSortPreserveKeys() : void
    {
        $array = [
            'user1' => ['name' => 'John', 'age' => 30],
            'user2' => ['name' => 'Jane', 'age' => 25],
            'user3' => ['name' => 'Bob', 'age' => 35]
        ];
        
        $result = Arrayify::sort($array, 'age', 'ASC', true);
        
        $keys = array_keys($result);
        $this->assertEquals('user2', $keys[0]); // Jane, age 25
        $this->assertEquals('user1', $keys[1]); // John, age 30
        $this->assertEquals('user3', $keys[2]); // Bob, age 35
    }

    /**
     * Test edge cases and error conditions.
     */
    public function testEdgeCases() : void
    {
        // Empty arrays
        $this->assertEquals([], Arrayify::merge());
        $this->assertEquals([], Arrayify::filter([]));
        $this->assertEquals([], Arrayify::values([]));
        $this->assertEquals([], Arrayify::keys([]));
        
        // Single element operations
        $single = ['only'];
        $this->assertEquals('only', Arrayify::shift($single));
        $this->assertEquals([], $single);
        
        $single = ['only'];
        $this->assertEquals('only', Arrayify::pop($single));
        $this->assertEquals([], $single);
    }

    /**
     * Test numeric array operations.
     */
    public function testNumericArrays() : void
    {
        $array = [10, 5, 20, 15, 25];
        
        // Test that numerical comparison works in sort
        $objects = [];
        foreach ($array as $value) {
            $objects[] = ['value' => $value];
        }
        
        $result = Arrayify::sort($objects, 'value');
        $values = Arrayify::map(function($item) { return $item['value']; }, $result);
        
        $this->assertEquals([5, 10, 15, 20, 25], $values);
    }
}
