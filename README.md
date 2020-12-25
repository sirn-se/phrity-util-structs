[![Build Status](https://travis-ci.com/sirn-se/phrity-util-structs.svg?branch=main)](https://travis-ci.com/sirn-se/phrity-util-structs)
[![Coverage Status](https://coveralls.io/repos/github/sirn-se/phrity-util-structs/badge.svg?branch=main)](https://coveralls.io/github/sirn-se/phrity-util-structs?branch=main)

# Structs utility

Utility library for objects and associative array. Recursive conversion, merge, diff, intersect, filter methods etc.

Current version supports PHP `^7.2|^8.0`.

## Installation

Install with [Composer](https://getcomposer.org/);
```
composer require phrity/util-structs
```

## General documentation

### Definitions

* An array is considered **Associative** if **any** key is non-integer.
* An array is considered **Sequential** if **all** keys are sequential integers, starting with `0`.

### Some notes on objects

Methods that evaluate [data sets](https://en.wikipedia.org/wiki/Algebra_of_sets) that contain objects might be subject to cloning.

Consider `A` operation `B`;
* If `A` exist but not `B`, `A` will be used as is
* If `B` exist but not `A`, `B` will be used as is
* If `A` and `B` are identical, `A` will be used as is
* If `A` and `B` are not identical, `A` will be **cloned** and `B` applied on the clone

That is, all input objects that remain unchanged will be retained as they were provided.
All input objects that are to be changed, will instead be cloned, leaving the original object unchanged.
This behaviour ensures that no objects in input are changed when calling these methods.

This strategy should be considered when providing class instances, as the `__clone()` method will be called on these.

Furthermore, only **public** properties will be considered in these methods.
If `A` is a class instance, **private** and **protected** properties will be retained,
but corresponding properties in `B` will not be evaluated. The same applies to **static** properties.

This strategy should be considered when providing class instances.

## The Structs class

### State evaluation methods

```php
public isAssociative(mixed $subject) : bool
public isSequential(mixed $subject) : bool
```

The `isAssociative` method will return true if `$subject` is an associative array (sse definitions above).
The `isSequential` method will return true if `$subject` is a sequential array (sse definitions above).

```php
$structs = new Structs();

$structs->isAssociative(['a' => 1, 'b' => 2]); // -> true
$structs->isAssociative([1, 'b' => 2]); // -> true
$structs->isAssociative([1, 2]); // -> false
$structs->isAssociative('Hello string'); // -> false
$structs->isAssociative((object)['a' => 1, 'b' => 2]); // -> false

$structs->isSequential([1, 2]); // -> true
$structs->isSequential(['a' => 1, 'b' => 2]); // -> false
$structs->isSequential([1 => 1, 2 => 2]); // -> false
$structs->isSequential('Hello string'); // -> false
$structs->isSequential((object)['a' => 1, 'b' => 2]); // -> false
```

### Conversion methods

```php
public toObject(mixed $subject) : mixed
```

The `toObject` will recursively convert all associative arrays (sse definitions above) to objects.

```php
$structs = new Structs();

$structs->toObject(['a' => 1, 'b' => 2]); // -> (object)['a' => 1, 'b' => 2]
$structs->toObject([1, 2]); // -> [1, 2]
$structs->toObject('Hello string'); // -> 'Hello string'
$structs->toObject([['a' => 1], ['b' => 2]]); // -> [(object)['a' => 1], (object)['b' => 2]]
```

### Data set methods

```php
public merge(mixed ...$subjects) : mixed
public filter(mixed $subject, [, callable|null $callback = null [, int $mode = 0]]) : mixed
```

The `merge` will recursively merge provided subjects.
* Array items with integer keys will always be appended
* Array items with associative keys will be replaced if corresponding key exist, otherwise appended
* Object property will be replaced if corresponding property name exist, otherwise appended

```php
$structs = new Structs();

$structs->merge([1, 2], [1, 3]); // -> [1, 2, 1, 3]
$structs->merge(['a' => 1, 'b' => 2], ['a' => 11, 'c' => 3]); // -> ['a' => 11, 'b' => 2, 'c' => 3]
$structs->merge((object)['a' => 1, 'b' => 2], (object)['a' => 11, 'c' => 3]); // -> (object)['a' => 11, 'b' => 2, 'c' => 3]
$structs->merge('Hello string'); // -> 'Hello string'
```

The `filter` method called without `$callback` will recursively remove all empty array items and object properties.
Empty arrays and objects without properties are considered empty, and will be removed accordingly.

If `$callback` is provided, it will be called for each array item and object property recursively.
If the `$callback` function return true, content will be kept, otherwise removed.
The `$mode` option defines how `$callback` will be called.
By default it's only called with the content of the array item or object property.
If set to `ARRAY_FILTER_USE_KEY` it will instead be called with array item key or object property name.
If set to `ARRAY_FILTER_USE_BOTH` it will instead be called with both content and key/name.

```php
$structs = new Structs();

$structs->filter([null, 0, 1, 'Hello', [0, 1], (object)['a' => 0, 'b' => 1]]);
// -> [1, 'Hello', [1], (object)['b' => 1]]
$structs->filter((object)['arr' => [0, null], 'obj' => (object)['a' => 0, 'b' => null], 'str' => 'Hello']);
// -> (object)['str' => 'Hello']

$structs->filter([1, 2, 3], function ($content) {
    // Called with content of each array item and object property
    return true;
});
$structs->filter([1, 2, 3], function ($key) {
    // Called with key/name of each array item and object property
    return true;
}, ARRAY_FILTER_USE_KEY);
$structs->filter([1, 2, 3], function ($content, $key) {
    // Called with content and key/name of each array item and object property
    return true;
}, ARRAY_FILTER_USE_BOTH);
```


### Traverse methods

```php
public walk(mixed $subject, callable $callback) : bool
```

The `walk` method will apply the callback function to each array item or object property in `$subject`.

```php
$structs = new Structs();
$structs->walk([1, 2], function ($value, $key) {
    // Called with 1, 0' followed by 2, 1
});
$structs->walk(['a' => 1, 'b' => 2], function ($value, $key) {
    // Called with 1, 'a' followed by 2, 'b'
});
$structs->walk((object)['a' => 1, 'b' => 2], function ($value, $key) {
    // Called with 1, 'a' followed by 2, 'b'
});
```


## Versions

| Version | PHP | |
| --- | --- | --- |
| `1.0` | `^7.2\|^8.0` | Initial version |
