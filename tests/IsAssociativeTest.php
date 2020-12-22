<?php

/**
 * File for Structs isAssociative function tests.
 * @package Phrity > Util > Structs
 */

declare(strict_types=1);

namespace Phrity\Util;

use Mock\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Structs isAssociative test class.
 */
class IsAssociativeTest extends TestCase
{
    /**
     * Set up for all tests
     */
    public function setUp(): void
    {
        error_reporting(-1);
    }

    /**
     * Test non-array input
     */
    public function testNonArrays(): void
    {
        $structs = new Structs();

        $this->assertFalse($structs->isAssociative(23));
        $this->assertFalse($structs->isAssociative('Hello string'));
        $this->assertFalse($structs->isAssociative(null));
        $this->assertFalse($structs->isAssociative((object)['a' => 1, 'b' => 2]));
        $this->assertFalse($structs->isAssociative(new stdClass()));
        $this->assertFalse($structs->isAssociative(new MockObject()));
    }

    /**
     * Test non-associative arrays
     */
    public function testNonAssociative(): void
    {
        $structs = new Structs();

        $this->assertFalse($structs->isAssociative([]));
        $this->assertFalse($structs->isAssociative([1, 2, 3]));
        $this->assertFalse($structs->isAssociative([1 => 'A', 5 => 'B', 2 => 'C']));
        $this->assertFalse($structs->isAssociative(['1' => 'A', 5.5 => 'B', 2 => 'C']));
    }

    /**
     * Test associative arrays
     */
    public function testAssociative(): void
    {
        $structs = new Structs();

        $this->assertTrue($structs->isAssociative(['a' => 1, 'b' => 2]));
        $this->assertTrue($structs->isAssociative([1, 2, 'a' => 3]));
    }
}
