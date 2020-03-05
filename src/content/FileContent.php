<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\content;

/**
 * Interface for actual file contents.
 *
 * @since  1.3.0
 */
interface FileContent
{
    /**
     * returns actual content
     */
    public function content(): string;

    /**
     * returns size of content
     */
    public function size(): int;

    /**
     * reads the given amount of bytes starting at offset
     */
    public function read(int $offset, int $count): string;

    /**
     * writes an amount of data starting at given offset
     */
    public function write(string $data, int $offset, int $length): void;

    /**
     * Truncates a file to a given length
     *
     * @param   int $size length to truncate file to
     */
    public function truncate(int $size): bool;
}
