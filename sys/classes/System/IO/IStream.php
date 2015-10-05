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
use \System\IDisposable;
use \System\IString;
use \System\ObjectDisposedException;
use \System\NotSupportedException;


/**
 * Describes a stream.
 *
 * @package System\IO
 * @author Marcel Joachim Kloubert <marcel.kloubert@gmx.net>
 */
interface IStream extends IDisposable {
    /**
     * Gets if the stream can be read.
     *
     * @return bool Can be read or not.
     */
    function canRead() : bool;

    /**
     * Gets if the stream is seekable or not.
     *
     * @return bool Can be seeked or not.
     */
    function canSeek() : bool;

    /**
     * Gets if the stream can be written.
     *
     * @return bool Can be written or not.
     */
    function canWrite() : bool;

    /**
     * Closes the stream.
     *
     * @throws IOException Operation failed.
     */
    function close();

    /**
     * Copies a stream to a target starting from the current position.
     *
     * @param IStream|resource $target The target.
     * @param int $bufferSize The buffer size to use.
     * @param bool $throwIfNotAllDataWereWritten Throw an exception if not all data from source could be written (true)
     *                                           or continue.
     *
     * @throws ArgumentException $target is no valid target.
     * @throws ArgumentNullException $target is (null).
     * @throws ArgumentOutOfRangeException $bufferSize is less than 1.
     * @throws IOException Copy operation failed, e.g. not all data could be written to $target.
     * @throws NotSupportedException Stream is not readable.
     * @throws ObjectDisposedException Stream has been disposed.
     * @throws StreamClosedException Stream has been closed.
     */
    function copyTo($target, int $bufferSize = 1024, bool $throwIfNotAllDataWereWritten = true);

    /**
     * Clears all buffers for this stream and causes any buffered data to be written to the underlying device.
     *
     * @throws IOException Operation failed.
     */
    function flush();

    /**
     * Gets if the stream has been closed or not.
     *
     * @return bool Has been closed or not.
     */
    function isClosed() : bool;

    /**
     * Gets if the stream has been disposed or not.
     *
     * @return bool Has been disposed or not.
     */
    function isDisposed() : bool;

    /**
     * Gets the length of the stream.
     *
     * @return int The length.
     *
     * @throws IOException Operation failed.
     * @throws NotSupportedException Stream is not seekable.
     * @throws ObjectDisposedException Stream has been disposed.
     * @throws StreamClosedException Stream has been closed.
     */
    function length() : int;

    /**
     * Gets the current position of the stream.
     *
     * @return int The current position.
     *
     * @throws IOException Operation failed.
     * @throws NotSupportedException Stream is not seekable.
     * @throws ObjectDisposedException Stream has been disposed.
     * @throws StreamClosedException Stream has been closed.
     */
    function position() : int;

    /**
     * Reads data from the stream
     *
     * @param int $count The number of data to read.
     *
     * @return IString The read data or (null) if no data was read.
     *
     * @throws ArgumentOutOfRangeException $count / $offset is less than 0.
     * @throws IOException Read operation failed.
     * @throws NotSupportedException Stream is not readable.
     * @throws ObjectDisposedException Stream has been disposed.
     * @throws StreamClosedException Stream has been closed.
     */
    function read(int $count);

    /**
     * Reads the next byte from the stream
     *
     * @return int|null The byte or (null) if no more data is available.
     *
     * @throws IOException Read operation failed.
     * @throws NotSupportedException Stream is not readable.
     * @throws ObjectDisposedException Stream has been disposed.
     * @throws StreamClosedException Stream has been closed.
     */
    function readByte();

    /**
     * Reads all remaining data.
     *
     * @return IString The read data.
     *
     * @throws ArgumentOutOfRangeException $bufferSize is less than 1.
     * @throws IOException Read operation failed.
     * @throws NotSupportedException Stream is not readable.
     * @throws ObjectDisposedException Stream has been disposed.
     * @throws StreamClosedException Stream has been closed.
     */
    function readToEnd(int $bufferSize = 1024) : IString;

    /**
     * Sets the new position for the stream.
     *
     * @param int $offset The offset.
     * @param int $where s. \fseek()
     *
     * @return int The new position.
     *
     * @throws ArgumentOutOfRangeException $where is invalid.
     * @throws IOException Operation failed.
     * @throws NotSupportedException Stream is not seekable.
     * @throws ObjectDisposedException Stream has been disposed.
     * @throws StreamClosedException Stream has been closed.
     */
    function seek(int $offset, int $where = \SEEK_SET) : int;

    /**
     * Sets the length of that stream.
     *
     * @param int $value The desired length of the current stream.
     *
     * @throws IOException Operation failed.
     * @throws NotSupportedException Stream is not seekable / writable.
     * @throws ObjectDisposedException Stream has been disposed.
     * @throws StreamClosedException Stream has been closed.
     */
    function setLength(int $value);

    /**
     * Writes to the stream.
     *
     * @param string $data The data to write.
     * @param int|null $count The number of bytes from $data to write.
     *                        (null) indicates that all data of $buffer from the $offset should be wriiten.
     * @param int $offset The zero based index inside $data from where to take the data.
     *
     * @return int The number of bytes that were written.
     *
     * @throws ArgumentException The combination of $offset and $count is invalid.
     * @throws ArgumentOutOfRangeException $count / $offset is less than 0.
     * @throws IOException Write operation failed.
     * @throws NotSupportedException Stream is not writable.
     * @throws ObjectDisposedException Stream has been disposed.
     * @throws StreamClosedException Stream has been closed.
     */
    function write($data, $count = null, int $offset = 0) : int;

    /**
     * Writes one or more bytes to the stream.
     *
     * @param int $byte The byte to write.
     *
     * @return bool Byte was written or not.
     *
     * @throws IOException Write operation failed.
     * @throws NotSupportedException Stream is not writable.
     * @throws ObjectDisposedException Stream has been disposed.
     * @throws StreamClosedException Stream has been closed.
     */
    function writeByte(int $byte) : bool;
}
