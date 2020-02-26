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
use const E_USER_WARNING;
use function trigger_error;

/**
 * Decorator for vfsErronousFile to allow multiple instances of a file to be open.
 *
 * Decorates a regular opened file and triggers errors when configured to do so.
 *
 * @internal
 */
class ErroneousOpenedFile implements FileHandle
{
    /** @var  FileHandle */
    private $openedFile;
    /** @var string[] */
    private $errorMessages;

    /**
     * @param string[] $errorMessages Formatted as [action => message], e.g. ['open' => 'error message']
     */
    public function __construct(FileHandle $openedFile, array $errorMessages)
    {
        $this->openedFile = $openedFile;
        $this->errorMessages = $errorMessages;
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
        if (isset($this->errorMessages['lock'])) {
            trigger_error($this->errorMessages['lock'], E_USER_WARNING);

            return false;
        }

        return $this->openedFile->lock($resource, $operation);
    }

    public function size(): int
    {
        if (isset($this->errorMessages['stat'])) {
            trigger_error($this->errorMessages['stat'], E_USER_WARNING);

            return -1;
        }

        return $this->openedFile->size();
    }

    /**
     * returns status of file
     *
     * @return int[]|false
     */
    public function stat()
    {
        return $this->openedFile->stat();
    }

    /**
     * reads the given amount of bytes from content
     *
     * Using this method changes the time when the file was last accessed.
     */
    public function read(int $count): string
    {
        if (isset($this->errorMessages['read'])) {
            trigger_error($this->errorMessages['read'], E_USER_WARNING);

            return '';
        }

        return $this->openedFile->read($count);
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
        if (isset($this->errorMessages['write'])) {
            trigger_error($this->errorMessages['write'], E_USER_WARNING);

            return 0;
        }

        return $this->openedFile->write($data);
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
        if (isset($this->errorMessages['truncate'])) {
            trigger_error($this->errorMessages['truncate'], E_USER_WARNING);

            return false;
        }

        return $this->openedFile->truncate($size);
    }

    /**
     * checks whether pointer is at end of file
     */
    public function eof(): bool
    {
        if (isset($this->errorMessages['eof'])) {
            trigger_error($this->errorMessages['eof'], E_USER_WARNING);

            // True on error.
            // See: https://www.php.net/manual/en/function.feof.php#refsect1-function.feof-returnvalues
            return true;
        }

        return $this->openedFile->eof();
    }

    /**
     * returns the current position within the file
     *
     * @internal  since 1.3.0
     */
    public function bytesRead(): int
    {
        if (isset($this->errorMessages['tell'])) {
            trigger_error($this->errorMessages['tell'], E_USER_WARNING);

            return 0;
        }

        return $this->openedFile->bytesRead();
    }

    /**
     * seeks to the given offset
     */
    public function seek(int $offset, int $whence): bool
    {
        if (isset($this->errorMessages['seek'])) {
            trigger_error($this->errorMessages['seek'], E_USER_WARNING);

            return false;
        }

        return $this->openedFile->seek($offset, $whence);
    }
}
