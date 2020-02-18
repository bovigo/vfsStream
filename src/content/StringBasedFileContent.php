<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\content;

use function class_alias;
use function str_repeat;
use function strlen;
use function substr;

/**
 * Default implementation for file contents based on simple strings.
 *
 * @since  1.3.0
 */
class StringBasedFileContent extends SeekableFileContent implements FileContent
{
    /**
     * actual content
     *
     * @var  string
     */
    private $content;

    /**
     * constructor
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * returns actual content
     */
    public function content(): string
    {
        return $this->content;
    }

    /**
     * returns size of content
     */
    public function size(): int
    {
        return strlen($this->content);
    }

    /**
     * actual reading of length starting at given offset
     */
    protected function doRead(int $offset, int $count): string
    {
        /** @var string|false $data */
        $data = substr($this->content, $offset, $count);

        return $data === false ? '' : $data;
    }

    /**
     * actual writing of data with specified length at given offset
     */
    protected function doWrite(string $data, int $offset, int $length): void
    {
        $this->content = substr($this->content, 0, $offset)
                       . $data
                       . substr($this->content, $offset + $length);
    }

    /**
     * Truncates a file to a given length
     *
     * @param   int $size length to truncate file to
     */
    public function truncate(int $size): bool
    {
        if ($size > $this->size()) {
            // Pad with null-chars if we're "truncating up"
            $this->content .= str_repeat("\0", $size - $this->size());
        } else {
            $this->content = substr($this->content, 0, $size);
        }

        return true;
    }
}

class_alias('bovigo\vfs\content\StringBasedFileContent', 'org\bovigo\vfs\content\StringBasedFileContent');
