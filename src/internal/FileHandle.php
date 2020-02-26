<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  bovigo\vfs
 */

namespace bovigo\vfs\internal;

use bovigo\vfs\StreamWrapper;

/**
 * Common interface for opened files.
 *
 * @internal
 */
interface FileHandle
{
    /**
     * locks file
     *
     * @see     https://github.com/mikey179/vfsStream/issues/6
     * @see     https://github.com/mikey179/vfsStream/issues/40
     *
     * @param resource|StreamWrapper $resource
     */
    public function lock($resource, int $operation): bool;

    /**
     * returns size of file in bytes
     */
    public function size(): int;

    /**
     * returns status of file
     *
     * @return int[]|false
     */
    public function stat();

    /**
     * reads the given amount of bytes from file
     *
     * Using this method changes the time when the file was last accessed.
     */
    public function read(int $count): string;

    /**
     * writes an amount of data
     *
     * Using this method changes the time when the file was last modified.
     *
     * @return  int     amount of written bytes
     */
    public function write(string $data): int;

    /**
     * Truncates a file to a given length
     *
     * @param int $size length to truncate file to
     *
     * @since   1.1.0
     */
    public function truncate(int $size): bool;

    /**
     * checks whether pointer is at end of file
     */
    public function eof(): bool;

    /**
     * returns the current position within the file
     *
     * @internal  since 1.3.0
     */
    public function bytesRead(): int;

    /**
     * seeks to the given offset
     */
    public function seek(int $offset, int $whence): bool;
}
