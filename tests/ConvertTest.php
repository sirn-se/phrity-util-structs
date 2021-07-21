<?php

/**
 * File for Structs convert function tests.
 * @package Phrity > Util > Structs
 */

declare(strict_types=1);

namespace Phrity\Util;

use Mock\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Structs toObject test class.
 */
class ConvertTest extends TestCase
{
    /**
     * Set up for all tests
     */
    public function setUp(): void
    {
        error_reporting(-1);
    }

    /**
     * Test atomic convert method
     */
    public function testConvert(): void
    {
        $structs = new Structs();

        $this->assertEquals(23, $structs->convert(23));
        $this->assertEquals('Hello string', $structs->convert('Hello string'));
        $this->assertNull($structs->convert(null));

        $this->assertEquals(
            [0 => 'a',  1 => 'b'],
            $structs->convert([1 => 'a',  3 => 'b'])
        );
        $this->assertEquals(
            (object)['a' => 1, 'b' => 2],
            $structs->convert(['a' => 1, 'b' => 2])
        );
        $this->assertEquals(
            (object)['a' => 1, 'b' => 2],
            $structs->convert((object)['a' => 1, 'b' => 2])
        );
        $this->assertEquals(
            (object)['my_public' => 'Public'],
            $structs->convert(new MockObject())
        );

        $this->assertNull($structs->convert(function () {
            return null;
        }));
        $this->assertNull($structs->convert(fopen(__FILE__, 'r')));
    }

    /**
     * Test recusive rConvert method on array root
     */
    public function testArrayConvert(): void
    {
        $structs = new Structs();

        $this->assertEquals(23, $structs->rConvert(23));

        $in = [
            23,
            'Hello string',
            [1 => 'a',  3 => 'b'],
            ['a' => 1, 'b' => 2],
            (object)['a' => 1, 'b' => 2],
            new MockObject(),
            [
                1 => 56,
                3 => [1 => 'c',  3 => 'd'],
                5 => (object)['a' => 11, 'b' => 22],
            ],
            function () {
                return null;
            },
            fopen(__FILE__, 'r'),
        ];
        $expect = [
            23,
            'Hello string',
            [0 => 'a',  1 => 'b'],
            (object)['a' => 1, 'b' => 2],
            (object)['a' => 1, 'b' => 2],
            (object)['my_public' => 'Public'],
            [
                0 => 56,
                1 => [0 => 'c',  1 => 'd'],
                2 => (object)['a' => 11, 'b' => 22],
            ],
            null,
            null,
        ];
        $this->assertEquals($expect, $structs->rConvert($in));
    }

    /**
     * Test recusive rConvert method on object root
     */
    public function testObjectConvert(): void
    {
        $structs = new Structs();

        $this->assertEquals(23, $structs->rConvert(23));

        $in = [
            'A' => 23,
            'B' => 'Hello string',
            'C' => [1 => 'a',  3 => 'b'],
            'D' => ['a' => 1, 'b' => 2],
            'E' => (object)['a' => 1, 'b' => 2],
            'F' => new MockObject(),
            'G' => [
                1 => 56,
                3 => [1 => 'c',  3 => 'd'],
                5 => (object)['a' => 11, 'b' => 22],
            ]
        ];
        $expect = (object)[
            'A' => 23,
            'B' => 'Hello string',
            'C' => [0 => 'a',  1 => 'b'],
            'D' => (object)['a' => 1, 'b' => 2],
            'E' => (object)['a' => 1, 'b' => 2],
            'F' => (object)['my_public' => 'Public'],
            'G' => [
                0 => 56,
                1 => [0 => 'c',  1 => 'd'],
                2 => (object)['a' => 11, 'b' => 22],
            ]
        ];
        $this->assertEquals($expect, $structs->rConvert($in));
    }
}
