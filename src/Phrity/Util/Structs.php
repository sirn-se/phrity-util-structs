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
    /* ---------- State evaluation methods ------------------------------------------- */

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
        foreach (array_keys($subject) as $key) {
            if (!is_int($key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * If provided subject is a sequential array (stricly sequential integer indexes).
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


    /* ---------- Conversion methods ------------------------------------------------- */

    /**
     * Recursivly convert associative arrays to objects.
     * @param mixed $subject Subject to convert
     * @return mixed Converted result
     */
    public function toObject($subject)
    {
        if ($this->isAssociative($subject)) {
            $subject = (object)$subject;
        }
        $this->walk($subject, function ($content, $key) use (&$subject) {
            if (is_array($subject)) {
                $subject[$key] = $this->toObject($content);
            } elseif (is_object($subject)) {
                $subject->$key = $this->toObject($content);
            }
        });
        return $subject;
    }


    /* ---------- Data set methods --------------------------------------------------- */

    /**
     * Recursivly merge two or more data sets.
     * @param mixed $subjects Subjects to merge
     * @return mixed Merge result
     */
    public function merge(...$subjects)
    {
        $result = array_shift($subjects);
        foreach ($subjects as $subject) {
            if ($this->isOverwrite($result, $subject)) {
                $result = $subject;
            } elseif (is_object($subject)) {
                $result = $this->mergeObjects($result, $subject);
            } elseif (is_array($subject)) {
                $result = $this->mergeArrays($result, $subject);
            }
        }
        return $result;
    }

    /**
     * Recursivly diff two or more data sets.
     * @param mixed $subjects Subjects to diff
     * @return mixed Diff result
     */
    public function diff(...$subjects)
    {
    }

    /**
     * Recursivly filter a data set.
     * @param mixed $subject Subject to filter
     * @param callable|null $callback The callback function to use
     * @param int $mode Flag determining what arguments are sent to callback
     * @return mixed Filter result
     */
    public function filter($subject, callable $callback = null, int $mode = 0)
    {
        // Create evaluation handler
        $evaluator = function ($key, $value) use ($callback, $mode) {
            // Default filter; remove empty scalars, arrays and objects
            if (is_null($callback)) {
                if (is_object($value)) {
                    $value = get_object_vars($value);
                }
                return !empty($value);
            }
            // Filter provided as callback; call according to flag
            switch ($mode) {
                case ARRAY_FILTER_USE_KEY:
                    return $callback($key);
                case ARRAY_FILTER_USE_BOTH:
                    return $callback($value, $key);
                default:
                    return $callback($value);
            }
        };
        // Run filter implementation method
        return $this->filterImpl($subject, $evaluator);
    }


    /* ---------- Traverse methods --------------------------------------------------- */

    /**
     * Walk subject.
     * @param mixed $subject Subject to walk
     * @param callable $callback The callback function to use
     * @return bool True
     */
    public function walk($subject, callable $callback)
    {
        if (is_iterable($subject)) {
            // Arrays and objects that implement Traversable
            $walk = $subject;
        } elseif (is_object($subject)) {
            // Other objects, only walk public properties
            $walk = get_object_vars($subject);
        } else {
            // Not something we can walk
            return true;
        }
        return array_walk($walk, $callback);
    }


    /* ---------- Private helper methods --------------------------------------------- */

    /**
     * Recursive merge two objects.
     * @param object $a Object to merge into
     * @param object $b Object to merge from
     * @return object Merge result
     */
    private function mergeObjects(object $a, object $b): object
    {
        if ($a == $b) {
            return $a;
        }
        if (is_object($a)) {
            $a = clone $a;
        }
        $this->walk($b, function ($content, $key) use ($a) {
            if (!isset($a->$key) || $this->isOverwrite($a->$key, $content)) {
                $a->$key = $content;
            } elseif (is_object($content)) {
                $a->$key = $this->mergeObjects($a->$key, $content);
            } elseif (is_array($content)) {
                $a->$key = $this->mergeArrays($a->$key, $content);
            }
        });
        return $a;
    }

    /**
     * Recursive merge two arrays.
     * @param array $a Array to merge into
     * @param array $b Array to merge from
     * @return array Merge result
     */
    private function mergeArrays(array $a, array $b): array
    {
        if ($a == $b) {
            return $a;
        }
        $this->walk($b, function ($content, $key) use (&$a) {
            if (is_int($key)) {
                $a = array_merge($a, [$content]);
            } elseif (!isset($a[$key]) || $this->isOverwrite($a[$key], $content)) {
                $a[$key] = $content;
            } elseif (is_object($content)) {
                $a[$key] = $this->mergeObjects($a[$key], $content);
            } elseif (is_array($content)) {
                $a[$key] = $this->mergeArrays($a[$key], $content);
            }
        });
        return $a;
    }

    /**
     * Recursive filter.
     * @param mixed $subject Subject to filter
     * @param callable $evaluator Filter method to apply
     * @return mixed Filter result
     */
    private function filterImpl($subject, callable $evaluator)
    {
        if (is_object($subject)) {
            $subject = clone $subject;
        }
        $this->walk($subject, function ($content, $key) use (&$subject, $evaluator) {
            if (is_array($subject)) {
                $content = $this->filterImpl($content, $evaluator);
                $subject[$key] = $content;
            } elseif (is_object($subject)) {
                $content = $this->filterImpl($content, $evaluator);
                $subject->$key = $content;
            }
            if ($evaluator($key, $content)) {
                return;
            }
            if (is_array($subject)) {
                unset($subject[$key]);
            } elseif (is_object($subject)) {
                unset($subject->$key);
            }
        });
        return $subject;
    }

    /**
     * Evalute if combination should overwrite.
     * @param mixed $a To comare
     * @param mixed $b To comare with
     * @return bool True if should overwrite
     */
    private function isOverwrite($a, $b): bool
    {
        return is_scalar($a) || is_scalar($b) || gettype($a) !== gettype($b);
    }
}
