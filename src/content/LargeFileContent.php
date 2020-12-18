<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\content;

use function array_filter;
use function array_keys;
use function class_alias;
use function substr;

/**
 * File content implementation to mock large files.
 *
 * When content is written via write() the data will be written into the
 * positions according to the current offset.
 * When content is read via read() it will use the already written data. If no
 * data is written at the offsets to read those offsets will be filled with
 * spaces.
 * Please note that accessing the whole content via content() will deliver a
 * string with the length of the originally defined size. It is not advisable to
 * do so with large sizes, except you have enough memory and time. :-)
 *
 * @since  1.3.0
 */
class LargeFileContent extends SeekableFileContent implements FileContent
{
    /**
     * byte array of written content
     *
     * @var string[]
     */
    private $content = [];
    /**
     * file size in bytes
     *
     * @var  int
     */
    private $size;

    /**
     * constructor
     *
     * @param  int $size file size in bytes
     */
    public function __construct(int $size)
    {
        $this->size = $size;
    }

    /**
     * create large file with given size in kilobyte
     */
    public static function withKilobytes(int $kilobyte): self
    {
        return new self($kilobyte * 1024);
    }

    /**
     * create large file with given size in megabyte
     */
    public static function withMegabytes(int $megabyte): self
    {
        return self::withKilobytes($megabyte * 1024);
    }

    /**
     * create large file with given size in gigabyte
     */
    public static function withGigabytes(int $gigabyte): self
    {
        return self::withMegabytes($gigabyte * 1024);
    }

    /**
     * returns actual content
     */
    public function content(): string
    {
        return $this->doRead(0, $this->size);
    }

    /**
     * returns size of content
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * actual reading of given byte count starting at given offset
     */
    protected function doRead(int $offset, int $count): string
    {
        if ($offset + $count > $this->size) {
            $count = $this->size - $offset;
        }

        $result = '';
        for ($i = 0; $i < $count; $i++) {
            $result .= $this->content[$i + $offset] ?? ' ';
        }

        return $result;
    }

    /**
     * actual writing of data with specified length at given offset
     */
    protected function doWrite(string $data, int $offset, int $length): void
    {
        for ($i = 0; $i < $length; $i++) {
            $this->content[$i + $offset] = substr($data, $i, 1);
        }

        if ($offset >= $this->size) {
            $this->size += $length;
        } elseif ($offset + $length > $this->size) {
            $this->size = $offset + $length;
        }
    }

    /**
     * Truncates a file to a given length
     *
     * @param   int $size length to truncate file to
     */
    public function truncate(int $size): bool
    {
        $this->size = $size;
        foreach (
            array_filter(
                array_keys($this->content),
                static function ($pos) use ($size) {
                                    return $pos >= $size;
                }
            ) as $removePos
        ) {
            unset($this->content[$removePos]);
        }

        return true;
    }
}

class_alias('bovigo\vfs\content\LargeFileContent', 'org\bovigo\vfs\content\LargeFileContent');
