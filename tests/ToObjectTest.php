<?php

/**
 * File for Structs toObject function tests.
 * @package Phrity > Util > Structs
 */

declare(strict_types=1);

namespace Phrity\Util;

use Mock\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Structs toObject test class.
 */
class ToObjectTest extends TestCase
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
    public function testNonArray(): void
    {
        $structs = new Structs();

        $this->assertEquals(23, $structs->toObject(23));
        $this->assertEquals('Hello string', $structs->toObject('Hello string'));
        $this->assertNull($structs->toObject(null));
        $data = (object)['a' => 1, 'b' => 2];
        $this->assertEquals($data, $structs->toObject($data));
        $data = new stdClass();
        $this->assertEquals($data, $structs->toObject($data));
        $data = new MockObject();
        $this->assertEquals($data, $structs->toObject($data));
    }

    /**
     * Test non-converted array
     */
    public function testNonConvertedArray(): void
    {
        $structs = new Structs();

        $data = [1, 2, 3];
        $this->assertEquals($data, $structs->toObject($data));
        $data = [1 => 'A', 2 => 'B', 3 => 'C'];
        $this->assertEquals($data, $structs->toObject($data));
        $data = ['1' => 'A', 5.5 => 'B', 2 => 'C'];
        $this->assertEquals($data, $structs->toObject($data));
    }

    /**
     * Test converted array
     */
    public function testConvertedArray(): void
    {
        $structs = new Structs();

        $data = ['a' => 1, 'b' => 2];
        $this->assertEquals((object)$data, $structs->toObject($data));
        $data = [1, 2, 'a' => 3];
        $this->assertEquals((object)$data, $structs->toObject($data));
    }

    /**
     * Test recusive conversion
     */
    public function testRecursion(): void
    {
        $structs = new Structs();

        $data = [
          'my_string' => 'Hello string',
          'my_int' => 23,
          'my_null' => null,
          'my_object' => (object)[
              'a' => 1,
              'b' => 2,
              'c' => 3,
              'obj' => (object)['aa' => 1, 'bb' => 2],
              'seq' => [1, 2],
              'ass' => ['aa' => 1, 'bb' => 2],
          ],
          'my_seq_array' => [
              1,
              2,
              3,
              (object)['aa' => 1, 'bb' => 2],
              [1, 2],
              ['aa' => 1, 'bb' => 2],
          ],
          'my_ass_array' => [
              'a' => 1,
              'b' => 2,
              'c' => 3,
              'obj' => (object)['aa' => 1, 'bb' => 2],
              'seq' => [1, 2],
              'ass' => ['aa' => 1, 'bb' => 2],
          ],
        ];
        $expected = (object)[
          'my_string' => 'Hello string',
          'my_int' => 23,
          'my_null' => null,
          'my_object' => (object)[
              'a' => 1,
              'b' => 2,
              'c' => 3,
              'obj' => (object)['aa' => 1, 'bb' => 2],
              'seq' => [1, 2],
              'ass' => (object)['aa' => 1, 'bb' => 2],
          ],
          'my_seq_array' => [
              1,
              2,
              3,
              (object)['aa' => 1, 'bb' => 2],
              [1, 2],
              (object)['aa' => 1, 'bb' => 2],
          ],
          'my_ass_array' => (object)[
              'a' => 1,
              'b' => 2,
              'c' => 3,
              'obj' => (object)['aa' => 1, 'bb' => 2],
              'seq' => [1, 2],
              'ass' => (object)['aa' => 1, 'bb' => 2],
          ],
        ];
        $this->assertEquals($expected, $structs->toObject($data));
    }
}
