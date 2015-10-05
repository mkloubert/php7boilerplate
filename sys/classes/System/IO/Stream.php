<?php

/**
 *  LINQ concept for PHP
 *  Copyright (C) 2015  Marcel Joachim Kloubert <marcel.kloubert@gmx.net>
 *
 *    This library is free software; you can redistribute it and/or
 *    modify it under the terms of the GNU Lesser General Public
 *    License as published by the Free Software Foundation; either
 *    version 3.0 of the License, or (at your option) any later version.
 *
 *    This library is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *    Lesser General Public License for more details.
 *
 *    You should have received a copy of the GNU Lesser General Public
 *    License along with this library.
 */

namespace System\IO;

use \System\ArgumentException;
use \System\ArgumentNullException;
use \System\ArgumentOutOfRangeException;
use \System\ClrString;
use \System\DisposableBase;
use \System\InvalidOperationException;
use \System\IString;
use \System\NotSupportedException;


/**
 * A common stream.
 *
 * @package System\IO
 * @author Marcel Joachim Kloubert <marcel.kloubert@gmx.net>
 */
class Stream extends DisposableBase implements IStream {
    /**
     * Close operation failed.
     */
    const ERROR_CLOSE_FAILED = 2;
    /**
     * Could not determine position.
     */
    const ERROR_COULD_NOT_DETERMINE_POSITION = 4;
    /**
     * Could not set position.
     */
    const ERROR_COULD_NOT_SET_POSITION = 5;
    /**
     * Not all data was written.
     */
    const ERROR_NOT_ALL_DATA_WRITTEN = 3;
    /**
     * Read operation failed.
     */
    const ERROR_READ_FAILED = 0;
    /**
     * Write operation failed.
     */
    const ERROR_WRITE_FAILED = 1;


    /**
     * @var bool
     */
    private $_closeOnDispose;
    /**
     * @var bool
     */
    private $_isClosed = false;
    /**
     * @var resource
     */
    private $_resource;


    /**
     * Initializes a new instance of that class.
     *
     * @param resource $resource The underlying resource.
     * @param bool $closeOnDispose Close resource when Stream::dispose() method is called or not.
     *
     * @throws ArgumentException $resource is no valid resource.
     */
    public function __construct($resource, bool $closeOnDispose = true) {
        if (!\is_resource($resource)) {
            throw new ArgumentException('resource');
        }

        $this->_closeOnDispose = $closeOnDispose;
        $this->_resource = $resource;
    }


    /**
     * Returns a value as stream.
     *
     * @param mixed $val The input value.
     *
     * @return IStream|null $val as stream or (null) if $val is also (null).
     *
     * @throws ArgumentException $val is invalid.
     */
    public static function asStream($val) {
        if ($val instanceof IStream) {
            return $val;
        }

        if (null === $val) {
            return null;
        }

        if (\is_resource($val)) {
            return new self($val, false);
        }

        if (ClrString::canBeString($val)) {
            return new MemoryStream($val);
        }

        throw new ArgumentException('val');
    }

    /**
     * {@inheritDoc}
     */
    public function canRead() : bool {
        $mode = $this->streamMode() ?? new ClrString();
        $mode->toLower()->trim();

        switch (\strval($mode)) {
            case 'a+':
            case 'c+':
            case 'r':
            case 'r+':
            case 'w+':
            case 'x+':
                return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function canSeek() : bool {
        $meta = $this->streamMeta();
        if (isset($meta['seekable'])) {
            return $meta['seekable'];
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function canWrite() : bool {
        $mode = $this->streamMode() ?? new ClrString();
        $mode->toLower()->trim();

        switch (\strval($mode)) {
            case 'a':
            case 'a+':
            case 'c':
            case 'c+':
            case 'r+':
            case 'w':
            case 'w+':
            case 'x':
            case 'x+':
                return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function close() {
        if ($this->isClosed()) {
            return;
        }

        if (!\fclose($this->_resource)) {
            $this->throwIOException('Could not close stream!', static::ERROR_CLOSE_FAILED);
        }

        $this->_isClosed = true;
    }

    /**
     * {@inheritDoc}
     */
    public function copyTo($target, int $bufferSize = 1024, bool $throwIfNotAllDataWereWritten = true) {
        $this->throwIfDisposed();
        $this->throwIfClosed();
        $this->throwIfNotReadable();

        $target = static::asStream($target);

        if (null === $target) {
            throw new ArgumentNullException('target');
        }

        if ($bufferSize < 1) {
            throw new ArgumentOutOfRangeException($bufferSize, 'bufferSize');
        }

        while (null !== ($data = $this->read($bufferSize))) {
            $dataLen = \strlen($data);

            $bytesWritten = $target->write($data);

            if ($throwIfNotAllDataWereWritten) {
                if ($bytesWritten < $dataLen) {
                    $this->throwIOException('Not all data could be written!', static::ERROR_NOT_ALL_DATA_WRITTEN);
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public final function flush() {
         $this->flushInner();
    }

    /**
     * @see Stream::flush()
     */
    protected function flushInner() {
        if (!\fflush($this->_resource)) {
            $this->throwIOException('Flush operation failed!');
        }
    }

    /**
     * {@inheritDoc}
     */
    public final function isClosed() : bool {
        return $this->_isClosed;
    }

    /**
     * {@inheritDoc}
     */
    public final function length() : int {
        $this->throwIfDisposed();
        $this->throwIfClosed();
        $this->throwIfNotSeekable();

        return $this->lengthInner();
    }

    /**
     * @see Stream::length()
     */
    protected function lengthInner() : int {
        throw new NotSupportedException('Could not determine length!');
    }

    /**
     * {@inheritDoc}
     */
    protected function onDispose(bool $disposing, bool &$isDisposed) {
        if (!$disposing) {
            return;
        }

        if (!$this->_closeOnDispose) {
            return;
        }

        $this->close();
    }

    /**
     * {@inheritDoc}
     */
    public final function position() : int {
        $this->throwIfDisposed();
        $this->throwIfClosed();
        $this->throwIfNotSeekable();

        return $this->positionInner();
    }

    /**
     * @see Stream::position()
     */
    protected function positionInner() : int {
        $result = \ftell($this->_resource);
        if (false === $result) {
            $this->throwIOException('Position could not be determined!', static::ERROR_COULD_NOT_DETERMINE_POSITION);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public final function read(int $count) {
        $this->throwIfDisposed();
        $this->throwIfClosed();
        $this->throwIfNotReadable();

        if ($count < 0) {
            throw new ArgumentOutOfRangeException($count, 'count');
        }

        if ($count < 1) {
            return null;
        }

        $result = $this->readInner($count);
        if ('' === $result) {
            $result = null;
        }

        return null !== $result ? new ClrString($result)
                                : null;
    }

    /**
     * @see Stream::read()
     */
    protected function readInner(int $count) {
        $result = \fread($this->_resource, $count);

        if (false === $result) {
            $this->throwIOException('Could not read from resource!', static::ERROR_READ_FAILED);
        }

        if ('' === $result) {
            $result = null;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public final function readByte() {
        $result = $this->read(1);

        return null !== $result ? \ord($result[0])
                                : null;
    }

    /**
     * {@inheritDoc}
     */
    public final function readToEnd(int $bufferSize = 1024) : IString {
        if ($bufferSize < 1) {
            throw new ArgumentOutOfRangeException($bufferSize, 'bufferSize');
        }

        $result = '';

        while (null !== ($data = $this->read($bufferSize))) {
            $result .= $data;
        }

        return new ClrString($result);
    }

    /**
     * {@inheritDoc}
     */
    public final function seek(int $offset, int $where = \SEEK_SET) : int {
        $this->throwIfDisposed();
        $this->throwIfClosed();
        $this->throwIfNotSeekable();

        switch ($where) {
            case \SEEK_SET:
            case \SEEK_CUR:
            case \SEEK_END:
                break;

            default:
                throw new ArgumentOutOfRangeException($where, 'where');
                break;
        }

        if (!$this->seekInner($offset, $where)) {
            $this->throwIOException('Setting new position failed!', static::ERROR_COULD_NOT_SET_POSITION);
        }

        return $this->position();
    }

    /**
     * @see Stream::seek()
     */
    protected function seekInner(int $offset, int $where) : bool {
        return 0 === \fseek($this->_resource, $offset, $where);
    }

    /**
     * {@inheritDoc}
     */
    public final function setLength(int $value) {
        $this->throwIfDisposed();
        $this->throwIfClosed();
        $this->throwIfNotWritable();
        $this->throwIfNotSeekable();

        $this->setLengthInner($value);
    }

    /**
     * @see Stream::setLength()
     */
    protected function setLengthInner(int $value) {
        if (!\ftruncate($this->_resource, $value)) {
            $this->throwIOException('Could not set length!');
        }
    }

    /**
     * Gets the meta data of the underlying resource.
     *
     * @return array The meta data.
     */
    public final function streamMeta() : array {
        return \stream_get_meta_data($this->_resource);
    }

    /**
     * Gets the mode of the stream.
     *
     * @return IString|null The mode or (null) if not available.
     */
    public final function streamMode() {
        $meta = $this->streamMeta();
        if (isset($meta['mode'])) {
            return new ClrString($meta['mode']);
        }

        return null;
    }

    /**
     * Throws an exception if the stream has been closed.
     *
     * @throws StreamClosedException Stream has been closed.
     */
    protected final function throwIfClosed() {
        if ($this->_isClosed) {
            throw new StreamClosedException($this);
        }
    }

    /**
     * @param string|null $message The custom message to use.
     * @param int $code The custom code to use.
     * @param \Exception|null $innerEx The optional inner exception to submit.
     *
     * @throws IOException The thrown exception.
     */
    protected final function throwIOException($message = null, int $code = 0, \Exception $innerEx = null) {
        throw new IOException($message, $innerEx, $code);
    }

    /**
     * Throws an exception if that stream is not readable.
     *
     * @throws InvalidOperationException Stream cannot be read.
     */
    protected final function throwIfNotReadable() {
        if (!$this->canRead()) {
            throw new NotSupportedException('Stream can not be read!');
        }
    }

    /**
     * Throws an exception if that stream is not seekable.
     *
     * @throws InvalidOperationException Stream cannot be seeked.
     */
    protected final function throwIfNotSeekable() {
        if (!$this->canSeek()) {
            throw new NotSupportedException('Stream can not be seeked!');
        }
    }

    /**
     * Throws an exception if that stream is not writeable.
     *
     * @throws InvalidOperationException Stream cannot be written.
     */
    protected final function throwIfNotWritable() {
        if (!$this->canWrite()) {
            throw new NotSupportedException('Stream can not be written!');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function toString() : IString {
        return $this->canRead() ? $this->readToEnd()
                                : parent::toString();
    }

    /**
     * {@inheritDoc}
     */
    public final function write($data, $count = null, int $offset = 0) : int {
        $this->throwIfDisposed();
        $this->throwIfClosed();
        $this->throwIfNotWritable();

        if ($offset < 0) {
            throw new ArgumentOutOfRangeException($offset, 'offset');
        }

        $data = ClrString::valueToString($data, false);

        if (null === $count) {
            $count = \strlen($data) - $offset;
        }
        else {
            $count = (int)$count;
        }

        if ($count < 0) {
            throw new ArgumentOutOfRangeException($count, 'count');
        }

        if ((\strlen($data) - $offset) < $count) {
            throw new ArgumentException('offset+count');
        }

        if ($count < 1) {
            return 0;
        }

        return $this->writeInner(\substr($data, $offset, $count));
    }

    /**
     * @see Stream::write()
     */
    protected function writeInner(string $data) : int {
        $result = \fwrite($this->_resource, $data);

        if (false === $result) {
            $this->throwIOException('Could not write to resource!', static::ERROR_WRITE_FAILED);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public final function writeByte(int $byte) : bool {
        return $this->write(\chr($byte)) > 0;
    }
}
