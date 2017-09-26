<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
namespace org\bovigo\vfs\content;
/**
 * Interface for actual file contents.
 *
 * @since  1.3.0
 */
interface FileContent
{
    /**
     * returns actual content
     *
     * @return  string
     */
    public function content(): string;

    /**
     * returns size of content
     *
     * @return  int
     */
    public function size(): int;

    /**
     * reads the given amount of bytes from content
     *
     * @param   int     $count
     * @return  string
     */
    public function read(int $count): string;

    /**
     * seeks to the given offset
     *
     * @param   int   $offset
     * @param   int   $whence
     * @return  bool
     */
    public function seek(int $offset, int $whence): bool;

    /**
     * checks whether pointer is at end of file
     *
     * @return  bool
     */
    public function eof(): bool;

    /**
     * writes an amount of data
     *
     * @param   string  $data
     * @return  amount of written bytes
     */
    public function write(string $data): int;

    /**
     * Truncates a file to a given length
     *
     * @param   int  $size length to truncate file to
     * @return  bool
     */
    public function truncate(int $size): bool;
}
