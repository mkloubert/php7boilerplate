<?php

/**********************************************************************************************************************
 * phpLINQ (https://github.com/mkloubert/phpLINQ)                                                                     *
 *                                                                                                                    *
 * Copyright (c) 2015, Marcel Joachim Kloubert <marcel.kloubert@gmx.net>                                              *
 * All rights reserved.                                                                                               *
 *                                                                                                                    *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the   *
 * following conditions are met:                                                                                      *
 *                                                                                                                    *
 * 1. Redistributions of source code must retain the above copyright notice, this list of conditions and the          *
 *    following disclaimer.                                                                                           *
 *                                                                                                                    *
 * 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the       *
 *    following disclaimer in the documentation and/or other materials provided with the distribution.                *
 *                                                                                                                    *
 * 3. Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote    *
 *    products derived from this software without specific prior written permission.                                  *
 *                                                                                                                    *
 *                                                                                                                    *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, *
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE  *
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, *
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR    *
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,  *
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE   *
 * USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.                                           *
 *                                                                                                                    *
 **********************************************************************************************************************/

namespace System\Collections;

use \System\ArgumentException;
use \System\ArgumentNullException;
use \System\ArgumentOutOfRangeException;
use \System\IObject;
use \System\IString;
use \System\Linq\IOrderedEnumerable;


/**
 * Describes a sequence.
 *
 * @package System\Collections
 * @author Marcel Joachim Kloubert <marcel.kloubert@gmx.net>
 */
interface IEnumerable extends \Countable, \Iterator, \Serializable, IObject {
    /**
     * Applies an accumulator function over the sequence.
     *
     * @param callable $accumulator An accumulator function to be invoked on each element.
     * @param mixed $defValue The value to return if sequence is empty.
     *
     * @return mixed The final accumulator value.
     *
     * @throws ArgumentException $predicate is no valid callable / lambda expression.
     * @throws ArgumentNullException $predicate is (null).
     */
    function aggregate($accumulator, $defValue = null);

    /**
     * Checks if all items match a condition.
     * An empty sequence will return (true).
     *
     * @param callable $predicate The predicate to use.
     *
     * @return bool All items match condition.
     *
     * @throws ArgumentException $predicate is no valid callable / lambda expression.
     * @throws ArgumentNullException $predicate is (null).
     */
    function all($predicate) : bool;

    /**
     * Checks if there is at least one element that matches a condition.
     *
     * @param callable $predicate The predicate to use.
     *                            If (null) at least one element must exist to return (true).
     *
     * @return bool One element found.
     *
     * @throws ArgumentException $predicate is no valid callable / lambda expression.
     */
    function any($predicate = null) : bool;

    /**
     * Appends the items of that sequence to an array.
     *
     * @param array $arr The target array.
     * @param bool $withKeys Also apply keys or not.
     *
     * @return IEnumerable That instance.
     */
    function appendToArray(array &$arr, bool $withKeys = false) : IEnumerable;

    /**
     * Calculates the average value of all values of that sequence.
     *
     * @param mixed $defValue The default value if sequence is empty.
     *
     * @return mixed The average value or the default.
     */
    function average($defValue = null);

    /**
     * Casts all items of that class to a specific type.
     *
     * @param string $type The target type.
     *
     * @return IEnumerable The new sequence.
     */
    function cast($type) : IEnumerable;

    /**
     * Concats the items of that sequence with one or more others.
     *
     * @param mixed ...$items The items to append.
     *
     * @return IEnumerable The new sequence.
     */
    function concat() : IEnumerable;

    /**
     * Concats all items of that sequence to one string.
     *
     * @param string $defValue The default value if sequence is empty.
     *
     * @return IString The sequence as string.
     */
    function concatToString($defValue = '') : IString;

    /**
     * Concats the items of that sequence with a list of values.
     *
     * @param mixed ...$value One or more value to append.
     *
     * @return IEnumerable The new sequence.
     */
    function concatValues() : IEnumerable;

    /**
     * Checks if that sequence contains an item.
     *
     * @param mixed $item The item to search for.
     * @param callable $equalityComparer The custom equality comparer to use.
     *
     * @return bool Contains item or not.
     */
    function contains($item, $equalityComparer = null) : bool;

    /**
     * Returns a default sequence if that sequence is empty.
     *
     * @param mixed $items The items for the default sequence.
     *
     * @return IEnumerable The default instance or that sequence if it is not empty.
     */
    function defaultArrayIfEmpty($items = null) : IEnumerable;

    /**
     * Returns a default sequence if that sequence is empty.
     *
     * @param mixed ...$item One or more item for the default sequence.
     *
     * @return IEnumerable The default instance or that sequence if it is not empty.
     */
    function defaultIfEmpty() : IEnumerable;

    /**
     * Removes duplicates.
     *
     * @param callable $equalityComparer The custom equality comparer.
     *
     * @return IEnumerable The new sequence.
     */
    function distinct($equalityComparer = null) : IEnumerable;

    /**
     * Iterates over that sequence by using a callable.
     *
     * @param callable $action The action to invoke for each item.
     * @param mixed $defResult The initial / default result.
     *
     * @return mixed The current result value from the iteration.
     *
     * @throws ArgumentException $action is no valid callable / lambda expression.
     * @throws ArgumentNullException $action is (null).
     */
    function each($action, $defResult = null);

    /**
     * Returns an element at a specific position.
     *
     * @param int $index The zero based index.
     * @param mixed $defValue The value to return if element was not found.
     *
     * @return mixed The element or the default value.
     */
    function elementAtOrDefault(int $index, $defValue = null);

    /**
     * Returns the items of that sequence except the items of other one.
     *
     * @param mixed $second The other sequence.
     * @param callable $equalityComparer The custom equaler function.
     *
     * @return IEnumerable The new sequence.
     *
     * @throws ArgumentException $equalityComparer is no valid callable / lambda expression.
     * @throws ArgumentNullException $equalityComparer is (null).
     */
    function except($second, $equalityComparer = null) : IEnumerable;

    /**
     * Returns the first matching value of that sequence or a default value if not found.
     *
     * @param mixed $predicateOrDefValue The custom predicate to use.
     *                                   If there is only one submitted argument and this variable contains
     *                                   no callable, it is set to (null) and its origin value is written to $defValue.
     * @param mixed $defValue The default value to return if value was not found.
     *
     * @return mixed The first matching value or the default value.
     */
    function firstOrDefault($predicateOrDefValue = null, $defValue = null);

    /**
     * Groups the items of that sequence.
     *
     * @param callable $keySelector The key selector.
     * @param callable $keyEqualityComparer The custom equality function for the keys.
     *
     * @return IEnumerable The grouped items as a sequence of IGrouping items.
     *
     * @throws ArgumentException $keySelector / $keyEqualityComparer is no valid callable / lambda expression.
     * @throws ArgumentNullException $keySelector is (null).
     */
    function groupBy($keySelector, $keyEqualityComparer = null) : IEnumerable;

    /**
     * Correlates the elements of that sequence and another based on matching keys and groups items.
     *
     * @param mixed $inner The other sequence.
     * @param callable $outerKeySelector The key selector for the items of that sequence.
     * @param callable $innerKeySelector The key selector for the items of the other sequence.
     * @param callable $resultSelector The function that provides the result value for two matching elements.
     * @param callable $keyEqualityComparer The custom equality function for the keys.
     *
     * @return static The joined sequence.
     *
     * @throws ArgumentException $outerKeySelector / $innerKeySelector / $resultSelector / $keyEqualityComparer is no valid callable / lambda expression.
     * @throws ArgumentNullException $outerKeySelector / $innerKeySelector / $resultSelector is (null).
     */
    function groupJoin($inner,
                       $outerKeySelector, $innerKeySelector,
                       $resultSelector,
                       $keyEqualityComparer = null);

    /**
     * Returns the intersection of this sequence and another.
     *
     * @param mixed $second The second sequence.
     * @param callable $equalityComparer The custom equaler function.
     *
     * @return IEnumerable The new sequence.
     *
     * @throws ArgumentException $equalityComparer is no valid callable / lambda expression.
     * @throws ArgumentNullException $equalityComparer is (null).
     */
    function intersect($second, $equalityComparer = null) : IEnumerable;

    /**
     * Gets if that sequence does not contain items anymore.
     *
     * @return bool Is empty or not.
     */
    function isEmpty() : bool;

    /**
     * Gets if that sequence still contains items or not.
     *
     * @return bool Is empty (false) or not (true).
     */
    function isNotEmpty() : bool;

    /**
     * Correlates the elements of that sequence and another based on matching keys.
     *
     * @param mixed $inner The other sequence.
     * @param callable $outerKeySelector The key selector for the items of that sequence.
     * @param callable $innerKeySelector The key selector for the items of the other sequence.
     * @param callable $resultSelector The function that provides the result value for two matching elements.
     * @param callable $keyEqualityComparer The custom equality function for the keys.
     *
     * @return IEnumerable The joined sequence.
     *
     * @throws ArgumentException $outerKeySelector / $innerKeySelector / $resultSelector / $keyEqualityComparer is no valid callable / lambda expression.
     * @throws ArgumentNullException $outerKeySelector / $innerKeySelector / $resultSelector is (null).
     */
    function join($inner,
                  $outerKeySelector, $innerKeySelector,
                  $resultSelector,
                  $keyEqualityComparer = null) : IEnumerable;

    /**
     * Joins all items of that sequence to one string by using a separator.
     *
     * @param mixed $separator The separator to use.
     * @param string $defValue The default value if sequence is empty.
     *
     * @return IString The sequence as string.
     */
    function joinToString($separator = null, $defValue = '') : IString;

    /**
     * Joins all items of that sequence to one string by using a separator.
     *
     * @param callable $separatorFactory The function that returns the separator to use.
     * @param string $defValue The default value if sequence is empty.
     *
     * @return IString The sequence as string.
     */
    function joinToStringCallback($separatorFactory = null, $defValue = '') : IString;

    /**
     * Returns the last matching value of that sequence or a default value if not found.
     *
     * @param mixed $predicateOrDefValue The custom predicate to use.
     *                                   If there is only one submitted argument and this variable contains
     *                                   no callable, it is set to (null) and its origin value is written to $defValue.
     * @param mixed $defValue The default value to return if value was not found.
     *
     * @return mixed The last matching value or the default value.
     */
    function lastOrDefault($predicateOrDefValue = null, $defValue = null);

    /**
     * Gets the maximum value of that sequence.
     *
     * @param mixed $defValue The default value if sequence is empty.
     * @param callable $comparer The custom comparer to use.
     *
     * @return mixed The maximum value.
     */
    function max($defValue = null, $comparer = null);

    /**
     * Gets the minimum value of that sequence.
     *
     * @param mixed $defValue The default value if sequence is empty.
     * @param callable $comparer The custom comparer to use.
     *
     * @return mixed The minimum value.
     */
    function min($defValue = null, $comparer = null);

    /**
     * Returns the items of a specific type.
     *
     * @param string $type The type.
     *
     * @return IEnumerable The new sequence.
     */
    function ofType($type) : IEnumerable;

    /**
     * Orders the items of that sequence ascending by using the items as sort value.
     *
     * @param callable $comparer The custom comparer to use.
     *
     * @return IOrderedEnumerable The new sequence.
     *
     * @throws ArgumentException $comparer is no valid callable / lambda expression.
     */
    function order($comparer = null) : IOrderedEnumerable;

    /**
     * Orders the items of that sequence ascending by using a specific sort value.
     *
     * @param callable|bool $selector The selector for the sort values.
     *                                (true) indicates to use the items itself as sort values.
     * @param callable $comparer The custom comparer to use.
     *
     * @return IOrderedEnumerable The new sequence.
     *
     * @throws ArgumentException $selector / $comparer is no valid callable / lambda expression.
     * @throws ArgumentNullException $selector is (null).
     */
    function orderBy($selector, $comparer = null) : IOrderedEnumerable;

    /**
     * Orders the items of that sequence descending by using a specific sort value.
     *
     * @param callable|bool $selector The selector for the sort values.
     *                                (true) indicates to use the items itself as sort values.
     * @param callable $comparer The custom comparer to use.
     *
     * @return IOrderedEnumerable The new sequence.
     *
     * @throws ArgumentException $selector / $comparer is no valid callable / lambda expression.
     * @throws ArgumentNullException $selector is (null).
     */
    function orderByDescending($selector, $comparer = null) : IOrderedEnumerable;

    /**
     * Orders the items of that sequence descending by using the items as sort value.
     *
     * @param callable $comparer The custom comparer to use.
     *
     * @return IOrderedEnumerable The new sequence.
     *
     * @throws ArgumentException $comparer is no valid callable / lambda expression.
     */
    function orderDescending($comparer = null) : IOrderedEnumerable;

    /**
     * Calculates the product of the items.
     *
     * @param mixed $defValue The default value if sequence is empty.
     *
     * @return mixed The product of the items.
     */
    function product($defValue = null);

    /**
     * Randomizes the order of that sequence.
     *
     * @param callable $seeder The custom function that initializes the random number generator.
     * @param callable $randProvider The custom function that provides the random values.
     *
     * @return IEnumerable The new sequence.
     *
     * @throws ArgumentException $seeder / $randProvider is no valid callable / lambda expression.
     */
    function randomize($seeder = null, $randProvider = null) : IEnumerable;

    /**
     * Resets the sequence and returns it.
     *
     * @return IEnumerable That instance.
     */
    function reset() : IEnumerable;

    /**
     * Returns the items of that sequence in reverse order.
     *
     * @return IOrderedEnumerable The new sequence.
     */
    function reverse() : IOrderedEnumerable;

    /**
     * Projects each element of that sequence to a new sequence.
     *
     * @param callable $selector The selector to use.
     *
     * @return IEnumerable The new sequence.
     *
     * @throws ArgumentException $selector is no valid callable / lambda expression.
     * @throws ArgumentNullException $selector is (null).
     */
    function select($selector) : IEnumerable;

    /**
     * Projects each element of that sequence to an IEnumerable
     * and flattens the resulting sequences into one sequence.
     *
     * @param callable $selector The selector to use.
     *
     * @return IEnumerable The new sequence.
     *
     * @throws ArgumentException $selector is no valid callable / lambda expression.
     * @throws ArgumentNullException $selector is (null).
     */
    function selectMany($selector) : IEnumerable;

    /**
     * Checks if another sequence has the same elements as that sequence.
     *
     * @param mixed $other The other sequence.
     * @param callable $equalityComparer The custom equality comparer to use.
     *
     * @return bool Both are equal or not.
     *
     * @throws ArgumentException $equalityComparer is no valid callable / lambda expression.
     */
    function sequenceEqual($other, $equalityComparer = null) : bool;

    /**
     * Returns the one and only matching element in that sequence.
     *
     * @param mixed $predicateOrDefValue The custom predicate to use.
     *                                   If there is only one submitted argument and this variable contains
     *                                   no callable, it is set to (null) and its origin value is written to $defValue.
     * @param mixed $defValue The default value if element was not found.
     *
     * @return mixed The found element or the default value.
     *
     * @throws \Exception Sequence contains more than one element.
     */
    function singleOrDefault($predicateOrDefValue = null, $defValue = null);

    /**
     * Skips a number of items.
     *
     * @param int $count The number of items to skip.
     *
     * @return IEnumerable The new sequence.
     *
     * @throws ArgumentOutOfRangeException $count is less than 0.
     */
    function skip(int $count) : IEnumerable;

    /**
     * Skips items while a condition matches.
     *
     * @param callable $predicate The predicate to use.
     *
     * @return IEnumerable The new sequence.
     *
     * @throws ArgumentException $predicate is no valid callable / lambda expression.
     * @throws ArgumentNullException $predicate is (null).
     */
    function skipWhile($predicate) : IEnumerable;

    /**
     * Calculates the sum of the items.
     *
     * @param mixed $defValue The default value if sequence is empty.
     *
     * @return mixed The sum of the items.
     */
    function sum($defValue = null);

    /**
     * Takes a number of items.
     *
     * @param int $count The number of items to take.
     *
     * @return IEnumerable The new sequence.
     *
     * @throws ArgumentOutOfRangeException $count is less than 0.
     */
    function take(int $count) : IEnumerable;

    /**
     * Takes items while a condition matches.
     *
     * @param callable $predicate The predicate to use.
     *
     * @return IEnumerable The new sequence.
     *
     * @throws ArgumentException $predicate is no valid callable / lambda expression.
     * @throws ArgumentNullException $predicate is (null).
     */
    function takeWhile($predicate) : IEnumerable;

    /**
     * Returns the items of that sequence as array.
     *
     * @param callable|bool $keySelector The custom key selector to use.
     *                                   If that value is (true), it will be use the keys from that sequence
     *                                   as array keys.
     *
     * @return array Items as array.
     *
     * @throws ArgumentException $keySelector is no valid callable / lambda expression.
     */
    function toArray($keySelector = null) : array;

    /**
     * Converts that sequence to a JSON string.
     *
     * @param callable|int $keySelectorOrOptions The key selector.
     *                                           If there is only one argumetn submitted and this argument is
     *                                           no callable, it is handled as value for $options and set to default.
     * @param int $options @see \json_encode()
     * @param int $depth @see \json_encode()
     *
     * @return IString The JSON string.
     */
    function toJson($keySelectorOrOptions = null, int $options = 0, int $depth = 512) : IString;

    /**
     * Produces the set union of that sequence and another.
     *
     * @param mixed $second The other sequence.
     * @param callable $equalityComparer The custom equality comparer to use.
     *
     * @return IEnumerable The new sequence.
     *
     * @throws ArgumentException $equalityComparer is no valid callable / lambda expression.
     */
    function union($second, $equalityComparer = null) : IEnumerable;

    /**
     * Filters the items of that sequence.
     *
     * @param callable $predicate The custom key selector to use.
     *
     * @return IEnumerable The new sequence.
     *
     * @throws ArgumentException $predicate is no valid callable / lambda expression.
     * @throws ArgumentNullException $predicate is (null).
     */
    function where($predicate) : IEnumerable;

    /**
     * Applies a specified function to the corresponding elements of that sequence and another,
     * producing a sequence of the results.
     *
     * @param mixed $second The second sequence.
     * @param callable $selector The selector that produces the result element from two input elements.
     *
     * @return IEnumerable The new sequence.
     *
     * @throws ArgumentException $selector is no valid callable / lambda expression.
     */
    function zip($second, $selector) : IEnumerable;
}
