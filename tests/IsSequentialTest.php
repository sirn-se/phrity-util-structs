<?php

/**
 * File for Structs isSequential function tests.
 * @package Phrity > Util > Structs
 */

declare(strict_types=1);

namespace Phrity\Util;

use Mock\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Structs isSequential test class.
 */
class IsSequentialTest extends TestCase
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

        $this->assertFalse($structs->isSequentialArray(23));
        $this->assertFalse($structs->isSequentialArray('Hello string'));
        $this->assertFalse($structs->isSequentialArray(null));
        $this->assertFalse($structs->isSequentialArray((object)['a' => 1, 'b' => 2]));
        $this->assertFalse($structs->isSequentialArray(new MockObject()));
    }

    /**
     * Test non-sequential arrays
     */
    public function testNonSequential(): void
    {
        $structs = new Structs();

        $this->assertFalse($structs->isSequentialArray(['a' => 1, 'b' => 2]));
        $this->assertFalse($structs->isSequentialArray([1 => 'A', 2 => 'B', 3 => 'C']));
        $this->assertFalse($structs->isSequentialArray(['1' => 'A', 5.5 => 'B', 2 => 'C']));
        $this->assertFalse($structs->isSequentialArray([1, 2, 'a' => 3]));
    }

    /**
     * Test sequential arrays
     */
    public function testSequential(): void
    {
        $structs = new Structs();

        $this->assertTrue($structs->isSequentialArray([]));
        $this->assertTrue($structs->isSequentialArray([1, 2, 3]));
        $this->assertTrue($structs->isSequentialArray([0 => 'A', 1 => 'B', 2 => 'C']));
    }
}
