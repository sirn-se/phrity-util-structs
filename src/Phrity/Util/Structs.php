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
    /* ---------- Type evaluation methods -------------------------------------------- */

    /**
     * If provided subject is an associative array (not only integer indexes).
     * @param mixed $subject Subject to check
     * @return bool
     */
    public function isAssociativeArray($subject): bool
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
    public function isSequentialArray($subject): bool
    {
        if (!is_array($subject)) {
            return false;
        }
        return $subject === [] || array_keys($subject) === range(0, count($subject) - 1);
    }

    /**
     * If is array and has requested key.
     * @param mixed $key Array key to look for
     * @param mixed $haystack Haystack to search
     * @return bool
     */
    public function hasArrayKey($key, $haystack): bool
    {
        return is_array($haystack) && array_key_exists($key, $haystack);
    }

    /**
     * If is object and has requested property.
     * @param mixed $key Property to look for
     * @param mixed $haystack Haystack to search
     * @return bool
     */
    public function hasProperty($key, $haystack): bool
    {
        return is_object($haystack) && property_exists($haystack, $key);
    }

    /**
     * If array contains value.
     * @param mixed $needle Value to look for
     * @param mixed $haystack Haystack to search
     * @return bool
     */
    public function inArray($needle, $haystack): bool
    {
        if (!is_array($haystack)) {
            return false;
        }
        if (is_object($needle)) {
            $needle = (array)$needle;
        }
        foreach ($haystack as $item) {
            if (is_object($item)) {
                $item = (array)$item;
            }
            if ($item == $needle) {
                return true;
            }
        }
        return false;
    }



    /* ---------- Conversion methods ------------------------------------------------- */

    /**
     * Converts input according to following rules;
     *  - Scalars are returned as is
     *  - Associative arrays are converted to objects
     *  - Non-associative arrays enforces sequential index
     *  - Objects returned as anonymous objects, only public properties
     *  - Other input types always return null
     * @param mixed $subject Subject to convert
     * @return mixed Converted subject
     */
    public function convert($subject)
    {
        if (is_scalar($subject)) {
            return $subject;
        }
        if ($this->isAssociativeArray($subject)) {
            return (object)$subject;
        }
        if (is_array($subject)) {
            return array_values($subject);
        }
        if (is_callable($subject)) {
            return null;
        }
        if (is_object($subject)) {
            return (object)get_object_vars($subject);
        }
        return null;
    }

    /**
     * Recursivly apply convert().
     * See convert() for conversion rules.
     * @param mixed $subject Subject to convert
     * @return mixed Converted subject
     */
    public function rConvert($subject)
    {
        return $this->map(function ($key, $content) {
            return $this->rConvert($content);
        }, $subject);
    }


    /* ---------- Intersection methods ----------------------------------------------- */

    /**
     * Intersect two inputs;
     *  - Scalars are returned if equal
     *  - Arrays return intersected content
     *  - Objects return intersection of property name and content
     *  - If any input is scalar and other is array, the scalar will be intersected on array
     *  - Other input types always return null
     * @param mixed $subject_a Subject to intersect
     * @param mixed $subject_b other subject to intersect
     * @return mixed Intersected subject
     */
    public function intersect($subject_a, $subject_b)
    {
        $subject_a = $this->convert($subject_a);
        $subject_b = $this->convert($subject_b);

        if ($subject_a === $subject_b) {
            return $subject_a;
        }
        if (is_array($subject_a) && is_scalar($subject_b)) {
            $subject_b = [$subject_b];
        }
        if (is_array($subject_b) && is_scalar($subject_a)) {
            $subject_a = [$subject_a];
        }
        if (is_array($subject_a) && is_array($subject_b)) {
            return array_values(array_filter($subject_a, function ($content) use ($subject_b) {
                return $this->inArray($content, $subject_b);
            }));
        }
        if (is_object($subject_a) && is_object($subject_b)) {
            return (object)array_filter((array)$subject_a, function ($content, $key) use ($subject_b) {
                return $this->hasProperty($key, $subject_b) && $content == $subject_b->$key;
            }, ARRAY_FILTER_USE_BOTH);
        }
        return null;
    }

    /**
     * Recursivly apply intersect().
     * See intersect() for intersction rules.
     * @param mixed $subject_a Subject to intersect
     * @param mixed $subject_b other subject to intersect
     * @return mixed Intersected subject
     */
    public function rIntersect($subject_a, $subject_b)
    {
        $subject = $this->intersect($subject_a, $subject_b);
        $reverse = $this->intersect($subject_b, $subject_a);
        return $this->map(function ($key, $content_a, $content_b) {
            if (is_scalar($content_a)) {
                return $content_a;
            }
            return $this->rIntersect($content_a, $content_b);
        }, $subject, $reverse);
    }


    /* ---------- Traverse methods --------------------------------------------------- */

    /**
     * Applies the callback to the elements of the subject.
     * @param mixed $subject Subject to map
     * @param callable $callback The callback function to apply
     * @return mixed Map result
     */
    public function map(callable $callback, $primary, $secondary = null)
    {
        $primary = $this->convert($primary);
        $secondary = $this->convert($secondary);
        if (is_array($primary)) {
            array_walk($primary, function ($content, $key) use (&$primary, $secondary, $callback) {
                $args = [$key, $content];
                if (isset($secondary)) {
                    $args[] = $this->hasArrayKey($key, $secondary) ? $secondary[$key] : null;
                }
                $primary[$key] = call_user_func_array($callback, $args);
            });
        } elseif (is_object($primary)) {
            array_walk($primary, function ($content, $key) use (&$primary, $secondary, $callback) {
                $args = [$key, $content];
                if (isset($secondary)) {
                    $args[] = $this->hasProperty($key, $secondary) ? $secondary->$key : null;
                }
                $primary->$key = call_user_func_array($callback, $args);
            });
        }
        return $primary;
    }







    /* ---------- IN PROGRESS -------------------------------------------------------- */

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
