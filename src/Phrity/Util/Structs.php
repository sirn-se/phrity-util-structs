<?php

/**
 * File for Structs utility class.
 * @package Phrity > Util > Structs
 */

namespace Phrity\Util;

/**
 * Structs utility class.
 * Utility library for objects and associative array.
 * Recursive conversion, merge, diff, intersect, filter methods etc.
 */
class Structs
{
    /**
     * If provided subject is an associative array (not only integer indexes).
     * @param mixed $subject Subject to check
     * @return bool
     */
    public function isAssociative($subject): bool
    {
        if (!is_array($subject)) {
            return false;
        }
        foreach ($subject as $key => $content) {
            if (!is_int($key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * If provided subject is a sequential array (stricly sequential integer indexes)
     * @param mixed $subject Subject to check
     * @return bool
     */
    public function isSequential($subject): bool
    {
        if (!is_array($subject)) {
            return false;
        }
        return $subject === [] || array_keys($subject) === range(0, count($subject) - 1);
    }

    /**
     * If provided subject is valid for array_walk function
     * @param mixed $subject Subject to check
     * @return bool
     */
    public function isWalkable($subject): bool
    {
        return is_iterable($subject) || is_object($subject);
    }


    // Evalute if combination should overwrite
    private function isOverwrite($a, $b): bool
    {
        return is_scalar($a) || is_scalar($b) || gettype($a) !== gettype($b);
    }
}
