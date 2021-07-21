<?php

/**
 * File for Structs isAssociative function tests.
 * @package Phrity > Util > Structs
 */

declare(strict_types=1);

namespace Phrity\Util;

use Mock\MockObject;
use PHPUnit\Framework\TestCase;

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

        $this->assertFalse($structs->isAssociativeArray(23));
        $this->assertFalse($structs->isAssociativeArray('Hello string'));
        $this->assertFalse($structs->isAssociativeArray(null));
        $this->assertFalse($structs->isAssociativeArray((object)['a' => 1, 'b' => 2]));
        $this->assertFalse($structs->isAssociativeArray(new MockObject()));
    }

    /**
     * Test non-associative arrays
     */
    public function testNonAssociative(): void
    {
        $structs = new Structs();

        $this->assertFalse($structs->isAssociativeArray([]));
        $this->assertFalse($structs->isAssociativeArray([1, 2, 3]));
        $this->assertFalse($structs->isAssociativeArray([1 => 'A', 5 => 'B', 2 => 'C']));
        $this->assertFalse($structs->isAssociativeArray(['1' => 'A', 5.5 => 'B', 2 => 'C']));
    }

    /**
     * Test associative arrays
     */
    public function testAssociative(): void
    {
        $structs = new Structs();

        $this->assertTrue($structs->isAssociativeArray(['a' => 1, 'b' => 2]));
        $this->assertTrue($structs->isAssociativeArray([1, 2, 'a' => 3]));
    }
}
