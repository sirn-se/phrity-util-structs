<?php

/**
 * File for Structs merge function tests.
 * @package Phrity > Util > Structs
 */

declare(strict_types=1);

namespace Phrity\Util;

use Mock\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Structs merge test class.
 */
class MergeTest extends TestCase
{
    /**
     * Set up for all tests
     */
    public function setUp(): void
    {
        error_reporting(-1);
    }

    /**
     * Test scalar merge
     */
    public function testMergeScalar(): void
    {
        $structs = new Structs();

        $this->assertEquals('I will replace you', $structs->merge('Hello string', 'I will replace you'));
        $this->assertEquals(23, $structs->merge(12, 9, 17, 23));
        $this->assertNull($structs->merge(12, 'Hello string', 17, null));

        return;
        $m1 = new MockObject();
        $m2 = new MockObject();
        $m2->hej = 'då';
        $apa = $structs->mergeObjects($m1, $m2);
        var_dump($apa);
    }

    /**
     * Test non-associative array merge
     */
    public function testNonAssociative(): void
    {
        $structs = new Structs();

        $this->assertEquals(
            [1, 3, 5, 7, 2, 4, 2, 6],
            $structs->merge([1, 3, 5, 7], [2, 4], [2, 6])
        );
        $this->assertEquals(
            array_merge(['a', 'c', 'e'], ['b', 'd'], ['b', 'e']),
            $structs->merge(['a', 'c', 'e'], ['b', 'd'], ['b', 'e'])
        );
    }

    /**
     * Test associative array merge
     */
    public function testAssociative(): void
    {
        $structs = new Structs();

        $this->assertEquals(
            ['a' => 11, 'c' => 44, 'b' => 2, 'd' => 4],
            $structs->merge(['a' => 1, 'c' => 3], ['b' => 2, 'c' => 44], ['a' => 11, 'd' => 4])
        );
        $this->assertEquals(
            array_merge([1 => 1, 3 => 3], ['a' => 1, 'b' => 2], ['a' => 11, 4, 5]),
            $structs->merge([1 => 1, 3 => 3], ['a' => 1, 'b' => 2], ['a' => 11, 4, 5])
        );
    }

     /**
     * Test object merge
     */
    public function testObject(): void
    {
        $structs = new Structs();

        $this->assertEquals(
            (object)['a' => 11, 'c' => 44, 'b' => 2, 'd' => 4],
            $structs->merge((object)['a' => 1, 'c' => 3], (object)['b' => 2, 'c' => 44], (object)['a' => 11, 'd' => 4])
        );

        $std1 = new stdClass();
        $std1->my_string = 'Hello string';
        $std2 = new stdClass();
        $std2->my_string = 'I will replace you';
        $this->assertEquals((object)['my_string' => 'I will replace you'], $structs->merge($std1, $std2));

        $m1 = new MockObject();
        $m2 = new MockObject();
        $m2->my_public = 'I will replace you';
        $m2->my_added = 'I will be added';
        $merged = $structs->merge($m1, $m2);
        $this->assertEquals('I will replace you', $merged->my_public);
        $this->assertEquals('I will be added', $merged->my_added);
    }

    /**
     * Test recursive merge
     */
    public function testRecursion(): void
    {
        $structs = new Structs();

        $data1 = (object)[
          'my_string' => 'Hello string',
          'my_int' => 23,
          'my_object' => (object)[
              'a' => 1,
              'b' => 2,
              'obj' => (object)['aa' => 1, 'bb' => 2],
              'seq' => [1, 2],
              'ass' => ['aa' => 1, 'bb' => 2],
          ],
          'my_seq_array' => [
              1,
              2,
              (object)['aa' => 1, 'bb' => 2],
              [1, 3],
              ['aa' => 1, 'bb' => 2],
          ],
          'my_ass_array' => [
              'a' => 1,
              'b' => 2,
              'obj' => (object)['aa' => 1, 'bb' => 2],
              'seq' => [1, 2],
              'ass' => ['aa' => 1, 'bb' => 2],
          ],
        ];
        $data2 = (object)[
          'my_string' => 'I will replace you',
          'my_null' => null,
          'my_object' => (object)[
              'b' => 22,
              'c' => 3,
              'obj' => (object)['bb' => 22, 'cc' => 3],
              'seq' => [1, 4],
              'ass' => ['bb' => 22, 'cc' => 3],
          ],
          'my_seq_array' => [
              1,
              3,
              (object)['bb' => 22, 'cc' => 3],
              [1, 3],
              ['bb' => 22, 'cc' => 3],
          ],
          'my_ass_array' => [
              'b' => 22,
              'c' => 3,
              'obj' => (object)['bb' => 22, 'cc' => 3],
              'seq' => [1, 3],
              'ass' => ['bb' => 22, 'cc' => 3],
          ],
        ];
        $expected = (object)[
          'my_string' => 'I will replace you',
          'my_int' => 23,
          'my_null' => null,
          'my_object' => (object)[
              'a' => 1,
              'b' => 22,
              'c' => 3,
              'obj' => (object)['aa' => 1, 'bb' => 22, 'cc' => 3],
              'seq' => [1, 2, 1, 4],
              'ass' => ['aa' => 1, 'bb' => 22, 'cc' => 3],
          ],
          'my_seq_array' => [
              1,
              2,
              (object)['aa' => 1, 'bb' => 2],
              [1, 3],
              ['aa' => 1, 'bb' => 2],
              1,
              3,
              (object)['bb' => 22, 'cc' => 3],
              [1, 3],
              ['bb' => 22, 'cc' => 3],
          ],
          'my_ass_array' => [
              'a' => 1,
              'b' => 22,
              'c' => 3,
              'obj' => (object)['aa' => 1, 'bb' => 22, 'cc' => 3],
              'seq' => [1, 2, 1, 3],
              'ass' => ['aa' => 1, 'bb' => 22, 'cc' => 3],
          ],
        ];
        $this->assertEquals($expected, $structs->merge($data1, $data2));
    }
}
