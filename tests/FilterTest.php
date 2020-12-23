<?php

/**
 * File for Structs filter function tests.
 * @package Phrity > Util > Structs
 */

declare(strict_types=1);

namespace Phrity\Util;

use Mock\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Structs filter test class.
 */
class FilterTest extends TestCase
{
    /**
     * Set up for all tests
     */
    public function setUp(): void
    {
        error_reporting(-1);
    }

    /**
     * Test scalar input
     */
    public function testScalar(): void
    {
        $structs = new Structs();

        $this->assertEquals('Hello string', $structs->filter('Hello string'));
        $this->assertEquals(23, $structs->filter(23));
        $this->assertNull($structs->filter(null));
        $this->assertNull($structs->filter(null));
    }

    /**
     * Test array input
     */
    public function testArray(): void
    {
        $structs = new Structs();
        $test = [1, null, 0, [], 'a', (object)[], (object)['a' => 11, 'b' => 'B', 'c' => null]];

        $this->assertEquals(
            [0 => 1, 4 => 'a', 6 => (object)['a' => 11, 'b' => 'B']],
            $structs->filter($test)
        );
        $this->assertEquals(
            [0 => 1, 2 => 0, 3 => [], 4 => 'a', 5 => (object)[], 6 => (object)['a' => 11, 'b' => 'B']],
            $structs->filter($test, function ($val) {
                return !is_null($val);
            })
        );
        $this->assertEquals(
            [0 => 1, 2 => 0, 4 => 'a', 6 => (object)[]],
            $structs->filter($test, function ($key) {
                return is_int($key) && $key % 2 === 0;
            }, ARRAY_FILTER_USE_KEY)
        );
        $this->assertEquals(
            [1 => null, 3 => [], 4 => 'a', 5 => (object)[], 6 => (object)[]],
            $structs->filter($test, function ($val, $key) {
                return is_int($key) && !is_int($val);
            }, ARRAY_FILTER_USE_BOTH)
        );
        $this->assertEquals(
            [1, null, 0, [], 'a', (object)[], (object)['a' => 11, 'b' => 'B', 'c' => null]],
            $test
        );
    }

     /**
     * Test object input
     */
    public function testObject(): void
    {
        $structs = new Structs();
        $test = (object)['a' => 1, 'b' => null, 'c' => 'a', 'd' => [0, 2], 'e' => (object)['aa' => 11, 'bb' => null]];

        $this->assertEquals(
            (object)['a' => 1, 'c' => 'a', 'd' => [1 => 2], 'e' => (object)['aa' => 11]],
            $structs->filter($test)
        );
        $this->assertEquals(
            (object)['a' => 1, 'c' => 'a', 'd' => [0, 2], 'e' => (object)['aa' => 11]],
            $structs->filter($test, function ($val) {
                return !is_null($val);
            })
        );
        $this->assertEquals(
            (object)['a' => 1, 'c' => 'a', 'e' => (object)['aa' => 11]],
            $structs->filter($test, function ($key) {
                return is_string($key) && ord($key) % 2 !== 0;
            }, ARRAY_FILTER_USE_KEY)
        );
        $this->assertEquals(
            (object)['a' => 1, 'b' => null, 'd' => [], 'e' => (object)['aa' => 11, 'bb' => null]],
            $structs->filter($test, function ($val, $key) {
                return is_string($key) && !is_string($val);
            }, ARRAY_FILTER_USE_BOTH)
        );
        $this->assertEquals(
            (object)['a' => 1, 'b' => null, 'c' => 'a', 'd' => [0, 2], 'e' => (object)['aa' => 11, 'bb' => null]],
            $test
        );
    }
}
