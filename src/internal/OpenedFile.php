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

use bovigo\vfs\vfsFile;
use bovigo\vfs\vfsStream;
use bovigo\vfs\content\FileContent;
use const SEEK_CUR;
use const SEEK_END;
use const SEEK_SET;

/**
 * Decorator for vfsFile to allow multiple instances of a file to be open.
 *
 * It works by tracking and restoring the position in the file for each specific
 * instance created, even though the underlying file is shared.
 *
 * @internal
 */
class OpenedFile
{
    /** @var  vfsFile */
    private $file;
    /** @var  FileContent */
    private $content;
    /** @var int */
    private $offset = 0;
    /**
     * mode the file was opened with
     *
     * @var  int
     */
    private $mode;

    public function __construct(vfsFile $file, FileContent $content, int $mode)
    {
        $this->file    = $file;
        $this->content = $content;
        $this->mode    = $mode;
    }

    public static function append(vfsFile $file, FileContent $content, int $mode): self
    {
        $s = new OpenedFile($file, $content, $mode);
        $s->offset = $content->size();
        return $s;
    }

    /**
     * locks file
     *
     * @see     https://github.com/mikey179/vfsStream/issues/6
     * @see     https://github.com/mikey179/vfsStream/issues/40
     *
     * @param resource|StreamWrapper $resource
     */
    public function lock($resource, int $operation): bool
    {
        return $this->file->lock($resource, $operation);
    }

    public function size(): int
    {
        return $this->file->size();
    }

    /**
     * returns status of file
     *
     * @return int[]|false
     */
    public function stat()
    {
        return $this->file->stat();
    }

    /**
     * reads the given amount of bytes from content
     *
     * Using this method changes the time when the file was last accessed.
     */
    public function read(int $count): string
    {
        if ($this->mode === Mode::WRITEONLY) {
            return '';
        }

        if ($this->file->isReadable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup()) === false) {
            return '';
        }

        $this->file->lastAccessed(time());
        $data = $this->content->read($this->offset, $count);
        $this->offset += $count;
        return $data;
    }

    /**
     * writes an amount of data
     *
     * Using this method changes the time when the file was last modified.
     *
     * @return  int     amount of written bytes
     */
    public function write(string $data): int
    {
        if ($this->mode === Mode::READONLY) {
            return 0;
        }

        if ($this->file->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup()) === false) {
            return 0;
        }

        $this->file->lastModified(time());

        $dataLength = strlen($data);
        $this->content->write($data, $this->offset, $dataLength);
        $this->offset += $dataLength;

        return $dataLength;
    }

    /**
     * Truncates a file to a given length
     *
     * @param int $size length to truncate file to
     *
     * @since   1.1.0
     */
    public function truncate(int $size): bool
    {
        if ($this->mode === Mode::READONLY) {
            return false;
        }

        if ($this->file->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup()) === false) {
            return false;
        }

        if ($this->file->type() !== Type::FILE) {
            return false;
        }

        $this->content->truncate($size);
        $this->file->lastModified(time());

        return true;
    }

    /**
     * checks whether pointer is at end of file
     */
    public function eof(): bool
    {
        return $this->content->size() <= $this->offset;
    }

    /**
     * returns the current position within the file
     *
     * @internal  since 1.3.0
     */
    public function bytesRead(): int
    {
        return $this->offset;
    }

    /**
     * seeks to the given offset
     */
    public function seek(int $offset, int $whence): bool
    {
        $newOffset = $this->offset;
        switch ($whence) {
            case SEEK_CUR:
                $newOffset += $offset;
                break;

            case SEEK_END:
                $newOffset = $this->content->size() + $offset;
                break;

            case SEEK_SET:
                $newOffset = $offset;
                break;

            default:
                return false;
        }

        if ($newOffset < 0) {
            return false;
        }

        $this->offset = $newOffset;

        return true;
    }
}