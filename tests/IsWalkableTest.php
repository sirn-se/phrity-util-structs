<?php

/**
 * File for Structs isWalkable function tests.
 * @package Phrity > Util > Structs
 */

declare(strict_types=1);

namespace Phrity\Util;

use Mock\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Structs isWalkable test class.
 */
class IsWalkableTest extends TestCase
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

        $this->assertFalse($structs->isWalkable(23));
        $this->assertFalse($structs->isWalkable('Hello string'));
        $this->assertFalse($structs->isWalkable(null));
    }

    /**
     * Test array input
     */
    public function testArray(): void
    {
        $structs = new Structs();

        $this->assertTrue($structs->isWalkable(['a' => 1, 'b' => 2]));
        $this->assertTrue($structs->isWalkable([1 => 'A', 2 => 'B', 3 => 'C']));
        $this->assertTrue($structs->isWalkable(['1' => 'A', 5.5 => 'B', 2 => 'C']));
        $this->assertTrue($structs->isWalkable([1, 2, 'a' => 3]));
    }

    /**
     * Test object input
     */
    public function testObject(): void
    {
        $structs = new Structs();

        $this->assertTrue($structs->isWalkable(new stdClass()));
        $this->assertTrue($structs->isWalkable((object)['a' => 1, 'b' => 2]));
        $this->assertTrue($structs->isWalkable(new MockObject()));
    }
}
