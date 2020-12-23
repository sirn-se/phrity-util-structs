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

    /**
     * If provided subject is valid for array_walk function.
     * @param mixed $subject Subject to check
     * @return bool
     */
    public function isWalkable($subject): bool
    {
        return is_iterable($subject) || is_object($subject);
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
        if ($this->isWalkable($subject)) {
            array_walk($subject, function ($content, $key) use (&$subject) {
                if ($this->isWalkable($content)) {
                    if (is_array($subject)) {
                        $subject[$key] = $this->toObject($content);
                    } else {
                        $subject->$key = $this->toObject($content);
                    }
                }
            });
        }
        return $subject;
    }


    /* ---------- Data set methods --------------------------------------------------- */

    /**
     * Recursivly merge two or more data sets.
     * @param mixed $subjects Subjects to convert
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
     * Recursivly filter a data set.
     * @param mixed $subject Subject to filter
     * @param callable|null $callback The callback function to use
     * @param int $mode Flag determining what arguments are sent to callback
     * @return mixed Filter result
     */
    public function filter($subject, callable $callback = null, int $mode = 0)
    {
        $evaluator = function ($key, $value) use ($callback, $mode) {
            if (is_null($callback)) {
                if (is_object($value)) {
                    $value = get_object_vars($value);
                }
                return !empty($value);
            }
            switch ($mode) {
                case ARRAY_FILTER_USE_KEY:
                    return $callback($key);
                case ARRAY_FILTER_USE_BOTH:
                    return $callback($value, $key);
                default:
                    return $callback($value);
            }
        };
        return $this->filterImpl($subject, $evaluator);
    }


    /* ---------- Private helper methods --------------------------------------------- */

    /**
     * Recursive filter.
     * @param mixed $subject Subject to filter
     * @param callable $evaluator Filter method to apply
     * @return mixed Filter result
     */
    private function filterImpl($subject, callable $evaluator)
    {
        if ($this->isWalkable($subject)) {
            if (is_object($subject)) {
                $subject = clone $subject;
            }
            array_walk($subject, function ($content, $key) use (&$subject, $evaluator) {
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
        }
        return $subject;
    }

    /**
     * Recursive merge two objects.
     * @param object $a Object to merge into
     * @param object $b Object to merge from
     * @return object Merge result
     */
    private function mergeObjects(object $a, object $b): object
    {
        if (is_object($a)) {
            $a = clone $a;
        }
        if (is_object($b)) {
            $b = get_object_vars($b); // Only public properties are merges
        }
        array_walk($b, function ($content, $key) use ($a) {
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
        array_walk($b, function ($content, $key) use (&$a) {
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
