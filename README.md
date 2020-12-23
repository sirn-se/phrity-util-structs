[![Build Status](https://travis-ci.com/sirn-se/phrity-util-structs.svg?branch=master)](https://travis-ci.com/sirn-se/phrity-util-structs)
[![Coverage Status](https://coveralls.io/repos/github/sirn-se/phrity-util-structs/badge.svg?branch=master)](https://coveralls.io/github/sirn-se/phrity-util-structs?branch=master)

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
* If `A` and `B` are identical, `A` will be used
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

### Conversion methods

### Data set methods

### Traverse methods



###  Class synopsis



## Versions

| Version | PHP | |
| --- | --- | --- |
| `1.0` | `^7.2\|^8.0` |  |
