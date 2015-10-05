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

namespace System;

use \System\Collections\IEnumerable;
use \System\Linq\Enumerable;
use \System\IO\IOException;
use \System\IO\IStream;
use \System\IO\MemoryStream;
use \System\IO\Stream;
use \System\IO\StreamClosedException;
use \System\Text\StringBuilder;


/**
 * A constant string.
 *
 * @package System
 * @author Marcel Joachim Kloubert <marcel.kloubert@gmx.net>
 */
class ClrString extends Enumerable implements IString {
    /**
     * The index value for something that was not found.
     */
    const NOT_FOUND_INDEX = -1;


    /**
     * @var string
     */
    protected $_wrappedValue;


    /**
     * Initializes a new instance of that class.
     *
     * @param mixed $value The value to wrap (as string).
     */
    public function __construct($value = '') {
        $this->_wrappedValue = static::valueToString($value);

        parent::__construct($this->createStringIterator());
    }

    /**
     * {@inheritDoc}
     */
    public final function __invoke() {
        return static::formatArray($this,
                                   \func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public final function append($value) : IString {
        return $this->appendArray(\func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public final function appendArray($values = null) : IString {
        $newValue = $this->_wrappedValue;

        foreach (\func_get_args() as $arg) {
            foreach (Enumerable::create($arg) as $v) {
                $newValue .= static::valueToString($v);
            }
        }

        return $this->transformWrappedValue($newValue);
    }

    /**
     * {@inheritDoc}
     */
    public final function appendBuffer($action, $startNewOrBufferFunc = true, $bufferFunc = null) : IString {
        static::updateBufferMethodArgs(\func_get_args(),
                                       $action, $startNewOrBufferFunc, $bufferFunc);

        return $this->append($this->invokeBufferFunc($action,
                                                     $startNewOrBufferFunc,
                                                     $bufferFunc));
    }

    /**
     * {@inheritDoc}
     */
    public final function appendFormat($format) : IString {
        return $this->append(\call_user_func_array([static::class, 'format'],
                                                   \func_get_args()));
    }

    /**
     * {@inheritDoc}
     */
    public final function appendFormatArray($format, $args = null) : IString {
        return $this->append(\call_user_func_array([static::class, 'formatArray'],
                                                   \func_get_args()));
    }

    /**
     * {@inheritDoc}
     */
    public final function appendLine($value = '', $newLine = null) : IString {
        static::updateNewLineValue(\func_num_args(), $newLine);

        return $this->appendArray([$value, $newLine]);
    }

    /**
     * {@inheritDoc}
     */
    public final function appendStream(IStream $stream, int $bufferSize = 1024, $count = null) : IString {
        /* @var IString $result */
        $result = null;

        $this->loadStreamData(function($data, IString $str) use (&$result) {
                                  $result = $str->append($data);
                              }, $stream, $bufferSize, $count);

        return null !== $result ? $result
                                : $this->transformWrappedValue();
    }

    /**
     * {@inheritDoc}
     */
    public function asImmutable() : IString {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function asMutable() : IMutableString {
        return new StringBuilder($this->_wrappedValue);
    }

    /**
     * Converts / casts a value to a IString object.
     *
     * @param mixed $val The value to convert / cast
     * @param bool $nullAsEmpty Handle (null) as empty string or not.
     * @param bool $mutable String should be mutable or not.
     *
     * @return IString|null The (new) string object or (null) if $val is also (null) AND
     *                      $nullAsEmpty has the value (false).
     */
    public static final function asString($val, bool $nullAsEmpty = true, bool $mutable = false) {
        if (null === $val) {
            if (!$nullAsEmpty) {
                return null;
            }
        }

        if (!$val instanceof IString) {
            $val = new self($val);
        }

        if ($mutable) {
            return $val->asMutable();
        }

        return $val;
    }

    /**
     * Checks if a value can be transformed to a string.
     *
     * @param mixed $val The value to check.
     *
     * @return bool Can be a string or not.
     */
    public static function canBeString($val) : bool {
        if (static::isString($val)) {
            return true;
        }

        if (\is_object($val)) {
            return \method_exists($val, '__toString');
        }

        if (\is_array($val)) {
            return false;
        }

        return false !== @\settype($val, 'string');
    }

    /**
     * {@inheritDoc}
     */
    public final function compareTo($other) : int {
        return \strcmp($this->_wrappedValue,
                       static::valueToString($other, false));
    }

    /**
     * {@inheritDoc}
     */
    public final function containsString($str, $ignoreCaseOrOffset = false, int $offset = 0) : bool {
        return \call_user_func_array([$this, 'indexOf'],
                                     \func_get_args()) > static::NOT_FOUND_INDEX;
    }

    /**
     * {@inheritDoc}
     */
    public final function count() {
        return \strlen($this->_wrappedValue);
    }

    /**
     * Creates an iterator for the current string value.
     *
     * @return \Iterator The created iterator.
     */
    protected function createStringIterator() : \Iterator {
        return new \ArrayIterator(\str_split($this->_wrappedValue));
    }

    /**
     * {@inheritDoc}
     */
    public final function endsWith($expr, bool $ignoreCase = false) : bool {
        $expr = static::valueToString($expr);

        return ('' === $expr) ||
               (($temp = $this->length() - \strlen($expr)) >= 0 &&
                 false !== $this->invokeFindStringFunc($expr, $ignoreCase, $temp));
    }

    /**
     * {@inheritDoc}
     */
    public final function equals($other) : bool {
        return static::valueToString($other, false) ===
               $this->getWrappedValue();
    }

    /**
     * Formats a string.
     *
     * @param string $format The format string.
     * @param mixed ...$arg One or more argument for $format.
     *
     * @return IString The formatted string.
     */
    public static final function format($format) : IString {
        return static::formatArray($format,
                                   \array_slice(\func_get_args(), 1));
    }

    /**
     * Formats a string.
     *
     * @param string $format The format string.
     * @param mixed ...$args One or more argument lists for $format.
     *
     * @return string The formatted string.
     */
    public static function formatArray($format, $args = null) : IString {
        if (!\is_array($args)) {
            $args = Enumerable::create($args)
                              ->toArray();
        }

        $argCount = \func_num_args();
        for ($i = 2; $i < $argCount; $i++) {
            Enumerable::create(\func_get_arg($i))
                      ->appendToArray($args);
        }

        return static::asString(\preg_replace_callback('/{(\d+)(\:[^}]*)?}/i',
                                                       function($match) use ($args) {
                                                           $i = (int)$match[1];

                                                           $format = null;
                                                           if (isset($match[2])) {
                                                               $format = \substr($match[2], 1);
                                                           }

                                                           return \array_key_exists($i, $args) ? ClrString::parseFormatStringValue($format, $args[$i])
                                                                                               : $match[0];
                                                       }, static::valueToString($format)));
    }

    /**
     * {@inheritDoc}
     */
    public final function getWrappedValue() : string {
        return $this->_wrappedValue;
    }

    /**
     * {@inheritDoc}
     */
    public final function indexOf($searchFor, $ignoreCaseOrOffset = false, int $offset = 0) : int {
        if (2 === \func_num_args()) {
            if (\is_int(\func_get_arg(1))) {
                $offset             = $ignoreCaseOrOffset;
                $ignoreCaseOrOffset = false;
            }
        }

        $result = $this->invokeFindStringFunc($searchFor, $ignoreCaseOrOffset, $offset, false);

        return false !== $result ? $result
                                 : static::NOT_FOUND_INDEX;
    }

    /**
     * {@inheritDoc}
     */
    public final function insert(int $startIndex, $value) : IString {
        $this->throwIfIndexOfOfRange($startIndex, true);

        $valueToInsert = '';

        $argCount = \func_num_args();
        for ($i = 1; $i < $argCount; $i++) {
            $valueToInsert .= static::valueToString(\func_get_arg($i));
        }

        return $this->transformWrappedValue(\substr($this->_wrappedValue, 0, $startIndex) .
                                            $valueToInsert .
                                            \substr($this->_wrappedValue, $startIndex));
    }

    /**
     * {@inheritDoc}
     */
    public final function insertArray(int $startIndex, $values) : IString {
        $this->throwIfIndexOfOfRange($startIndex, true);

        $argCount = \func_num_args();
        for ($i = 1; $i < $argCount; $i++) {
            $valueToInsert = '';

            foreach (Enumerable::create(\func_get_arg($i)) as $v) {
                $valueToInsert .= static::valueToString($v);
            }

            $this->_wrappedValue = (string)$this->insert($startIndex, $valueToInsert);
        }

        return $this->transformWrappedValue();
    }

    /**
     * {@inheritDoc}
     */
    public final function insertBuffer(int $startIndex, $action, $startNewOrBufferFunc = true, $bufferFunc = null) : IString {
        $this->throwIfIndexOfOfRange($startIndex, true);

        static::updateBufferMethodArgs(\func_get_args(),
                                       $action, $startNewOrBufferFunc, $bufferFunc);

        return $this->insert($startIndex, $this->invokeBufferFunc($action,
                                                                  $startNewOrBufferFunc,
                                                                  $bufferFunc));
    }

    /**
     * {@inheritDoc}
     */
    public final function insertLine(int $startIndex, $value = '', $newLine = null) : IString {
        static::updateNewLineValue(\func_num_args() - 1, $newLine);

        return $this->insertArray($startIndex, [$value, $newLine]);
    }

    /**
     * {@inheritDoc}
     */
    public final function insertStream(int $startIndex, IStream $stream, int $bufferSize = 1024, $count = null) : IString {
        $this->throwIfIndexOfOfRange($startIndex, true);

        $valueToInsert = '';

        $this->loadStreamData(function($data) use (&$valueToInsert) {
                                  $valueToInsert .= $data;
                              }, $stream, $bufferSize, $count);

        return $this->insert($startIndex, $valueToInsert);
    }

    /**
     * Invokes a buffered function.
     *
     * @param callable $func The function to invoke.
     * @param bool $startNew Start new buffer or not.
     * @param callable $bufferFunc The optional callable for the NEW buffer.
     *
     * @return string The final content of the buffer.
     */
    protected function invokeBufferFunc(callable $func, bool $startNew, $bufferFunc) {
        $actionResult = null;

        try {
            if ($startNew) {
                if (null === $bufferFunc) {
                    \ob_start();
                }
                else {
                    \ob_start($bufferFunc);
                }
            }

            $actionResult = static::valueToString($func($this), false);
        }
        finally {
            $result = \ob_get_contents();

            if ($startNew) {
                \ob_end_clean();
            }
        }

        if (!static::isNullOrEmpty($actionResult)) {
            $result .= $actionResult;
        }

        return $result;
    }

    /**
     * Invokes the function for finding a string.
     *
     * @param string &$expr The expression to search for.
     * @param bool $ignoreCase Ignore case or not.
     * @param int $offset The offset.
     * @param bool $findLast Find last occurrence (true) or first (false).
     *
     * @return mixed The result of the invocation.
     */
    protected final function invokeFindStringFunc(&$expr = null, bool $ignoreCase = false, int $offset = 0, bool $findLast = false) {
        $expr = static::valueToString($expr);
        $str  = $this->getWrappedValue();

        if (!$findLast) {
            $func = !$ignoreCase ? "\\strpos" : "\\stripos";
        }
        else {
            $func = !$ignoreCase ? "\\strrpos" : "\\strripos";
        }

        return \call_user_func($func,
                               $str, $expr, $offset);
    }

    /**
     * Invokes a replace function for that string.
     *
     * @param string $oldValue The old value.
     * @param $newValue The new value.
     * @param bool $ignoreCase Ignore case or not.
     * @param int &$count The variable were to write down how many replacements happend.
     *
     * @return IString The (new) string.
     */
    protected final function invokeReplaceFunc($oldValue, $newValue, bool $ignoreCase, &$count) : IString {
        $func = !$ignoreCase ? "\\str_replace" : "\\str_ireplace";

        return $this->transformWrappedValue(\call_user_func_array($func,
                                                                  [static::valueToString($oldValue),
                                                                   static::valueToString($newValue),
                                                                   $this->_wrappedValue, &$count]));
    }

    /**
     * Invokes the \str_pad() function for that string.
     *
     * @param int $pad_type s. \str_pad()
     * @param int $pad_length s. \str_pad()
     * @param $pad_string s. \str_pad(); (null) will be converted to ' '
     *
     * @return IString The (new) string.
     */
    protected final function invokeStrPadFunc(int $pad_type, int $pad_length, $pad_string) : IString {
        $pad_string = static::valueToString($pad_string, false);
        if (null === $pad_string) {
            $pad_string = ' ';
        }

        return $this->transformWrappedValue(\str_pad($this->_wrappedValue,
                                                     $pad_length,
                                                     $pad_string,
                                                     $pad_type));
    }

    /**
     * {@inheritDoc}
     */
    public final function isEmpty() : bool {
        return \strlen($this->_wrappedValue) < 1;
    }

    /**
     * {@inheritDoc}
     */
    public final function isImmutable() : bool {
        return !$this->isMutable();
    }

    /**
     * {@inheritDoc}
     */
    public function isMutable() : bool {
        return false;
    }

    /**
     * Checks if a string is (null) or empty.
     *
     * @param string $str The string to check.
     *
     * @return bool Is (null) / empty or not.
     */
    public static final function isNullOrEmpty($str) : bool {
        $str = static::valueToString($str, false);

        return (null === $str) ||
               (\strlen($str) < 1);
    }

    /**
     * Checks if a string is (null) or contains whitespaces only.
     *
     * @param string $str The string to check.
     * @param string $character_mask The custom list of whitespace characters to use.
     *
     * @return bool Is (null) / empty or not.
     */
    public static final function isNullOrWhitespace($str, $character_mask = null) : bool {
        if (null === $str) {
            return true;
        }

        if (!$str instanceof IString) {
            $str = new self($str);
        }

        return $str->isWhitespace($character_mask);
    }

    /**
     * Checks if a value is a string.
     *
     * @param mixed $val The value to check.
     *
     * @return bool Is string or not.
     */
    public static function isString($val) : bool {
        return \is_string($val) ||
               ($val instanceof IString);
    }

    /**
     * {@inheritDoc}
     */
    public final function isWhitespace($character_mask = null) : bool {
        if (null === $character_mask) {
            $trimmed = \trim($this->_wrappedValue);
        }
        else {
            $trimmed = \trim($this->_wrappedValue, $character_mask);
        }

        return \strlen($trimmed) < 1;
    }

    /**
     * {@inheritDoc}
     */
    public final function lastIndexOf($searchFor, $ignoreCaseOrOffset = false, int $offset = 0) : int {
        if (2 === \func_num_args()) {
            if (\is_int(\func_get_arg(1))) {
                $offset             = $ignoreCaseOrOffset;
                $ignoreCaseOrOffset = false;
            }
        }

        $result = $this->invokeFindStringFunc($searchFor, $ignoreCaseOrOffset, $offset, true);

        return false !== $result ? $result
                                 : static::NOT_FOUND_INDEX;
    }

    /**
     * {@inheritDoc}
     */
    public final function length() : int {
        return \strlen($this->_wrappedValue);
    }

    /**
     * Loads data from a stream.
     *
     * @param callable $onDataArrived The event that is raised when data has arrived.
     * @param IStream $stream
     * @param int $bufferSize The buffer size for read operation to use.
     * @param int|null $count The maximum number of bytes to load.
     *
     * @throws ArgumentOutOfRangeException $bufferSize is less than 1
     *                                     -- or ---
     *                                     $count is defined and less than 0.
     * @throws IOException Read operation failed.
     * @throws NotSupportedException Stream is not readable.
     * @throws ObjectDisposedException Stream has been disposed.
     * @throws StreamClosedException Stream has been closed.
     */
    protected function loadStreamData(callable $onDataArrived, IStream $stream, int $bufferSize, $count) {
        if ($bufferSize < 1) {
            throw new ArgumentOutOfRangeException($bufferSize, 'bufferSize');
        }

        if (null !== $count) {
            if ($count < 0) {
                throw new ArgumentOutOfRangeException($count, 'count');
            }
        }

        $me = $this;

        $invokeOnDataArrived = function($data) use ($me, $onDataArrived, $stream) {
            $onDataArrived($data, $me, $stream);
        };

        if (null === $count) {
            $invokeOnDataArrived($stream->readToEnd($bufferSize));
        }
        else {
            while ($count > 0) {
                $dataSize = $count;
                if ($dataSize > $bufferSize) {
                    $dataSize = $bufferSize;
                }

                $data = $stream->read($dataSize);
                if (null === $data) {
                    // no more data
                    break;
                }

                $invokeOnDataArrived($data);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public final function offsetExists($index) {
        return isset($this->_wrappedValue[$index]);
    }

    /**
     * {@inheritDoc}
     */
    public final function offsetGet($index) {
        $this->throwIfIndexOfOfRange($index);

        return $this->_wrappedValue[$index];
    }

    /**
     * {@inheritDoc}
     */
    public final function offsetSet($index, $value) {
        $this->throwIfNotMutable();

        if (null !== $index) {
            $this->offsetUnset($index);
            $this->transformWrappedValue($this->insert($index, $value)
                                              ->getWrappedValue());
        }
        else {
            $this->transformWrappedValue($this->append($value)
                                              ->getWrappedValue());
        }
    }

    /**
     * {@inheritDoc}
     */
    public final function offsetUnset($index) {
        $this->throwIfNotMutable();
        $this->throwIfIndexOfOfRange($index);

        $this->transformWrappedValue($this->remove($index, 1)
                                          ->getWrappedValue());
    }

    /**
     * {@inheritDoc}
     */
    public final function pad(int $pad_length, $pad_string = null) : IString {
        return $this->invokeStrPadFunc(\STR_PAD_BOTH, $pad_length, $pad_string);
    }

    /**
     * {@inheritDoc}
     */
    public final function padLeft(int $pad_length, $pad_string = null) : IString {
        return $this->invokeStrPadFunc(\STR_PAD_LEFT, $pad_length, $pad_string);
    }

    /**
     * {@inheritDoc}
     */
    public final function padRight(int $pad_length, $pad_string = null) : IString {
        return $this->invokeStrPadFunc(\STR_PAD_RIGHT, $pad_length, $pad_string);
    }

    /**
     * Formats a value for a formatted string.
     *
     * @param string $format The format string for $value.
     * @param mixed $value The value to parse.
     *
     * @return mixed The parsed value.
     */
    public static function parseFormatStringValue($format, $value) {
        $format = static::valueToString($format, false);

        if (null !== $format) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format($format);
            }
            else if ($value instanceof \DateInterval) {
                return $value->format($format);
            }

            // default
            return \sprintf($format, $value);
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public final function prepend($value) : IString {
        return $this->prependArray(\func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public final function prependArray($values = null) : IString {
        $newValue = $this->_wrappedValue;

        foreach (\func_get_args() as $arg) {
            $newValue = Enumerable::create($arg)
                                  ->concatToString() . $newValue;
        }

        return $this->transformWrappedValue($newValue);
    }

    /**
     * {@inheritDoc}
     */
    public final function prependBuffer($action, $startNewOrBufferFunc = true, $bufferFunc = null) : IString {
        static::updateBufferMethodArgs(\func_get_args(),
                                       $action, $startNewOrBufferFunc, $bufferFunc);

        return $this->prepend($this->invokeBufferFunc($action,
                                                      $startNewOrBufferFunc,
                                                      $bufferFunc));
    }

    /**
     * {@inheritDoc}
     */
    public final function prependFormat($format) : IString {
        return $this->prepend(\call_user_func_array([static::class, 'format'],
                                                    \func_get_args()));
    }

    /**
     * {@inheritDoc}
     */
    public final function prependFormatArray($format, $args = null) : IString {
        return $this->prepend(\call_user_func_array([static::class, 'formatArray'],
                                                    \func_get_args()));
    }

    /**
     * {@inheritDoc}
     */
    public final function prependLine($value = '', $newLine = null) : IString {
        static::updateNewLineValue(\func_num_args(), $newLine);

        return $this->prependArray([$value, $newLine]);
    }

    /**
     * {@inheritDoc}
     */
    public final function prependStream(IStream $stream, int $bufferSize = 1024, $count = null) : IString {
        $valueToPrepend = '';

        $this->loadStreamData(function($data) use (&$valueToPrepend) {
                                  $valueToPrepend .= $data;
                              }, $stream, $bufferSize, $count);

        return $this->prepend($valueToPrepend);
    }

    /**
     * {@inheritDoc}
     */
    public final function remove(int $startIndex, $count = null) : IString {
        $argCount = \func_num_args();

        if ($startIndex < 0) {
            throw new ArgumentOutOfRangeException($startIndex, 'startIndex');
        }

        if ($argCount > 1) {
            if ($count < 0) {
                throw new ArgumentOutOfRangeException($count, 'count');
            }

            if ($count > ($this->length() - $startIndex)) {
                throw new ArgumentOutOfRangeException([$count, $startIndex], 'count+startIndex');
            }
        }

        $newValue = \substr($this->_wrappedValue, 0, $startIndex);

        if ($argCount > 1) {
            $newValue .=  \substr($this->_wrappedValue,
                                  $startIndex + $count,
                                  \strlen($this->_wrappedValue) - $startIndex - $count);
        }

        return $this->transformWrappedValue($newValue);
    }

    /**
     * {@inheritDoc}
     */
    public final function replace($oldValue, $newValue, bool $ignoreCase = false, &$count = null) : IString {
        return $this->invokeReplaceFunc($oldValue, $newValue,
                                        $ignoreCase,
                                        $count);
    }

    /**
     * {@inheritDoc}
     */
    public final function serialize() : string {
        return $this->_wrappedValue;
    }

    /**
     * {@inheritDoc}
     */
    public final function similarity($other, bool $ignoreCase = false, bool $doTrim = false, $character_mask = null) : float {
        $argCount = \func_num_args();

        $thisString = $this->_wrappedValue;
        $other      = static::valueToString($other);

        if ($ignoreCase) {
            $thisString = \strtolower($thisString);
            $other      = \strtolower($other);
        }

        if ($doTrim) {
            $trimString = function($str) use ($argCount, $character_mask) {
                $args = [$str];
                if ($argCount > 3) {
                    $args[] = static::valueToString($character_mask, false);
                }

                return \call_user_func_array("\\trim", $args);
            };

            $thisString = $trimString($thisString);
            $other      = $trimString($other);
        }

        \similar_text($thisString, $other, $result);

        return (float)$result / 100.0;
    }

    /**
     * {@inheritDoc}
     */
    public final function split($delimiter, $limit = null) : IEnumerable {
        $delimiter = static::valueToString($delimiter, false);
        $str = $this->_wrappedValue;

        if (\func_num_args() < 2) {
            $result = Enumerable::create(\explode($delimiter, $str));
        }
        else {
            $result = Enumerable::create(\explode($delimiter, $str, $limit));
        }

        return $result->select(static::format('$x => new \{0}($x)',
                                              static::class));
    }

    /**
     * {@inheritDoc}
     */
    public final function startsWith($expr, bool $ignoreCase = false) : bool {
        return 0 === $this->invokeFindStringFunc($expr, $ignoreCase);
    }

    /**
     * {@inheritDoc}
     */
    public final function subString(int $startIndex, $length = null) : IString {
        if (\func_num_args() < 2) {
            $newStr = \substr($this->_wrappedValue, $startIndex);
        }
        else {
            $newStr = \substr($this->_wrappedValue, $startIndex, $length);
        }

        return $this->transformWrappedValue($newStr);
    }

    /**
     * Throws an exception if an index is out of range.
     *
     * @param int $index The value to check.
     * @param bool $allowStringLength Allow length of string as valid value or not.
     *
     * @throws ArgumentOutOfRangeException $index is out of range.
     */
    protected final function throwIfIndexOfOfRange(int $index, bool $allowStringLength = false) {
        $outOfLengthVal = \strlen($this->_wrappedValue);
        if ($allowStringLength) {
            ++$outOfLengthVal;
        }

        if (($index < 0) || ($index >= $outOfLengthVal)) {
            throw new ArgumentOutOfRangeException($index, 'index');
        }
    }

    /**
     * Throws an exception if that string is NOT mutable.
     *
     * @throws NotSupportedException String is NOT mutable.
     */
    protected final function throwIfNotMutable() {
        if (!$this->isMutable()) {
            throw new NotSupportedException('String is NOT mutable!');
        }
    }

    /**
     * {@inheritDoc}
     */
    public final function toArray($keySelector = null) : array {
        return static::createEnumerable($this->_wrappedValue)
                     ->toArray($keySelector);
    }

    /**
     * {@inheritDoc}
     */
    public final function toCharArray() : array {
        return \str_split($this->_wrappedValue);
    }

    /**
     * {@inheritDoc}
     */
    public final function toLower() : IString {
        return $this->transformWrappedValue(\strtolower($this->_wrappedValue));
    }

    /**
     * {@inheritDoc}
     */
    public final function toUpper() : IString {
        return $this->transformWrappedValue(\strtoupper($this->_wrappedValue));
    }

    /**
     * {@inheritDoc}
     */
    public function toString() : IString {
        return new static($this->_wrappedValue);
    }

    /**
     * Transforms the wrapped value.
     *
     * @param string $newValue The new value. If not defined, nothing will change.
     *
     * @return IString The (new) string.
     */
    protected function transformWrappedValue($newValue = null) : IString {
        if (\func_num_args() < 1) {
            $newValue = $this->_wrappedValue;
        }

        $result = new static($newValue);

        // try to move to same position
        while ($result->valid()) {
            if ($result->key() === $this->key()) {
                break;
            }

            $result->next();
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public final function trim($character_mask = null) : IString {
        return $this->trimMe("\\trim", $character_mask);
    }

    /**
     * {@inheritDoc}
     */
    public final function trimEnd($character_mask = null) : IString {
        return $this->trimMe("\\rtrim", $character_mask);
    }

    /**
     * Trims the string.
     *
     * @param callable $func The function to use.
     * @param string $character_mask The custom char list that represent whitespaces.
     *
     * @return IString The (new) string.
     */
    protected final function trimMe(callable $func, $character_mask) : IString {
        if (null === $character_mask) {
            $trimmed = $func($this->_wrappedValue);
        }
        else {
            $trimmed = $func($this->_wrappedValue, $character_mask);
        }

        return $this->transformWrappedValue($trimmed);
    }

    /**
     * {@inheritDoc}
     */
    public final function trimStart($character_mask = null) : IString {
        return $this->trimMe("\\ltrim", $character_mask);
    }

    /**
     * {@inheritDoc}
     */
    public final function unserialize($str) {
        $this->__construct($str);
    }

    /**
     * Updates the arguments for a "buffer method"
     *
     * @param array $args The arguments that were submitted to the method.
     * @param callable &$action The variable for the buffer action.
     * @param bool $startNew The variable with the value that indicates if new buffer should be started or not.
     * @param callable $bufferFunc The value for the buffer callback.
     *
     * @throws ArgumentNullException $action / $bufferFunc is no valid callable / lambda expression.
     */
    protected static function updateBufferMethodArgs(array $args, &$action, &$startNew, &$bufferFunc) {
        if (null === $action) {
            throw new ArgumentNullException('action');
        }

        $action = static::asCallable($action);

        if (2 === count($args)) {
            if (static::isCallable($args[1])) {
                // swap values

                $bufferFunc = $args[1];
                $startNew   = true;
            }
        }

        $bufferFunc = static::asCallable($bufferFunc);
    }

    /**
     * Updates the "new line" value for a suitable method.
     *
     * @param int $argCount The number of arguments that were submitted to the method.
     * @param string &$newLine The variable that contains the "new line" value.
     */
    protected static function updateNewLineValue(int $argCount, &$newLine) {
        if ($argCount < 2) {
            $newLine = "\n";
        }

        if (true === $newLine) {
            $newLine = \PHP_EOL;
        }
    }

    /**
     * Updates the inner iterator.
     */
    protected final function updateStringIterator() {
        $this->_i = $this->createStringIterator();
    }

    /**
     * Converts a value to a PHP string.
     *
     * @param mixed $value The input value.
     * @param bool $nullAsEmpty Handle (null) as empty or not.
     *
     * @return string $value as string.
     *
     * @throws InvalidCastException Cannot convert value to string.
     */
    public static function valueToString($value, bool $nullAsEmpty = true) {
        $value = static::getRealValue($value);

        if (\is_string($value)) {
            return $value;
        }

        if (null === $value) {
            return $nullAsEmpty ? '' : null;
        }

        if (!static::canBeString($value)) {
            throw new InvalidCastException('string', $value, null, null, 0);
        }

        if (false === @\settype($value, 'string')) {
            throw new InvalidCastException('string', $value, null, null, 1);
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public final function writeTo(&$target, int $bufferSize = 1024) : IString {
        if (null === $target) {
            throw new ArgumentNullException('target');
        }

        if ($bufferSize < 1) {
            throw new ArgumentOutOfRangeException($bufferSize, 'bufferSize');
        }

        if ($target instanceof IString) {
            $target = $target->append($this->_wrappedValue);
        }
        else {
            static::using(function(IStream $src, IStream $dest, int $buffSize) {
                $src->copyTo($dest, $buffSize);
            }, new MemoryStream($this->_wrappedValue)
             , Stream::asStream($target), $bufferSize);
        }

        return $this;
    }
}
