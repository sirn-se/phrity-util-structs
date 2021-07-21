<?php

/**
 * File for Structs intersect function tests.
 * @package Phrity > Util > Structs
 */

declare(strict_types=1);

namespace Phrity\Util;

use Mock\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Structs toObject test class.
 */
class IntersectTest extends TestCase
{
    /**
     * Set up for all tests
     */
    public function setUp(): void
    {
        error_reporting(-1);
    }

    /**
     * Test atomic intersect method
     */
    public function testIntersect(): void
    {
        $structs = new Structs();

        $this->assertEquals(23, $structs->intersect(23, 23));
        $this->assertEquals('Hello string', $structs->intersect('Hello string', 'Hello string'));
        $this->assertNull($structs->intersect(null, null));

        $this->assertEquals(
            [0 => 'a',  1 => 'b'],
            $structs->intersect([1 => 'a',  3 => 'b'], [1 => 'a',  3 => 'b'])
        );
        $this->assertEquals(
            (object)['a' => 1, 'b' => 2],
            $structs->intersect(['a' => 1, 'b' => 2], ['a' => 1, 'b' => 2])
        );
        $this->assertEquals(
            (object)['my_public' => 'Public'],
            $structs->intersect(new MockObject(), new MockObject())
        );

        $this->assertEquals([2, 4], $structs->intersect([1, 2, 3, 4], [0, 2, 4, 6]));
        $this->assertEquals([2], $structs->intersect([1, 2, 3, 4], 2));
        $this->assertEquals([4], $structs->intersect(4, [0, 2, 4, 6]));
        $this->assertEquals(
            (object)['a' => 1],
            $structs->intersect(['a' => 1, 'b' => 2], ['a' => 1, 'b' => 3])
        );

        $this->assertNull($structs->intersect(function () {
            return null;
        }, function () {
             return null;
        }));
        $this->assertNull($structs->intersect(fopen(__FILE__, 'r'), fopen(__FILE__, 'r')));
    }

    /**
     * Test recurive rIntersect method on array root
     */
    public function testArrayIntersect(): void
    {
        $structs = new Structs();

        $this->assertEquals(23, $structs->intersect(23, 23));

        $in_1 = [
            10,
            20,
            30,
           'Hello string',
            [1 => 'a',  3 => 'b'],
            ['a' => 1, 'b' => 2],
            (object)['c' => 1, 'd' => 2],
            (object)['c' => 2, 'd' => 1],
            new MockObject(),
            [
                1 => 56,
                3 => [1 => 'c',  3 => 'd'],
                5 => (object)['a' => 11, 'b' => 22],
            ],
        ];
        $in_2 = [
            40,
            30,
            20,
            'Hello string',
            'Not me',
            [1 => 'a',  3 => 'b'],
            ['a' => 1, 'b' => 2],
            (object)['c' => 1, 'd' => 2],
            new MockObject(),
            [
                1 => 56,
                3 => [1 => 'c',  3 => 'd'],
                5 => (object)['a' => 11, 'b' => 22],
            ],
        ];
        $expect = [
            20,
            30,
            'Hello string',
            [0 => 'a',  1 => 'b'],
            (object)['a' => 1, 'b' => 2],
            (object)['c' => 1, 'd' => 2],
            (object)['my_public' => 'Public'],
            [
                0 => 56,
                1 => [0 => 'c',  1 => 'd'],
                2 => (object)['a' => 11, 'b' => 22],
            ],
        ];
        $this->assertEquals($expect, $structs->rIntersect($in_1, $in_2));
    }

    /**
     * Test recurive rIntersect method on object root
     */
    public function testObjectIntersect(): void
    {
        $structs = new Structs();

        $this->assertEquals(23, $structs->intersect(23, 23));

        $in_1 = [
            'A' => 10,
            'B' => 20,
            'C' => 30,
            'D' => 'Hello string',
            'E' => [1 => 'a',  3 => 'b'],
            'F' => ['a' => 1, 'b' => 2],
            'G' => (object)['c' => 1, 'd' => 2],
            'H' => (object)['c' => 2, 'd' => 1],
            'I' => new MockObject(),
            'J' => [
                1 => 56,
                3 => [1 => 'c',  3 => 'd'],
                5 => (object)['a' => 11, 'b' => 22],
            ],
        ];
        $in_2 = [
            'K' => 40,
            'C' => 30,
            'B' => 20,
            'D' => 'Hello string',
            'L' => 'Not me',
            'E' => [1 => 'a',  3 => 'b'],
            'F' => ['a' => 1, 'b' => 2],
            'G' => (object)['c' => 1, 'd' => 2],
            'I' => new MockObject(),
            'J' => [
                1 => 56,
                3 => [1 => 'c',  3 => 'd'],
                5 => (object)['a' => 11, 'b' => 22],
            ],
        ];
        $expect = (object)[
            'B' => 20,
            'C' => 30,
            'D' => 'Hello string',
            'E' => [0 => 'a',  1 => 'b'],
            'F' => (object)['a' => 1, 'b' => 2],
            'G' => (object)['c' => 1, 'd' => 2],
            'I' => (object)['my_public' => 'Public'],
            'J' => [
                0 => 56,
                1 => [0 => 'c',  1 => 'd'],
                2 => (object)['a' => 11, 'b' => 22],
            ],
        ];
        $this->assertEquals($expect, $structs->rIntersect($in_1, $in_2));
    }
}
