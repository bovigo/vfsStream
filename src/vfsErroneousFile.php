<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs;

use bovigo\vfs\internal\ErroneousOpenedFile;
use bovigo\vfs\internal\FileHandle;
use const E_USER_WARNING;
use function trigger_error;

/**
 * File to trigger errors on specific actions.
 *
 * Allows for throwing an error during fopen, fwrite, etc.
 *
 * @api
 */
class vfsErroneousFile extends vfsFile
{
    /** @var string[] */
    private $errorMessages;

    /**
     * @param string[] $errorMessages Formatted as [action => message], e.g. ['open' => 'error message']
     * @param int|null $permissions   optional
     */
    public function __construct(string $name, array $errorMessages, ?int $permissions = null)
    {
        parent::__construct($name, $permissions);

        $this->errorMessages = $errorMessages;
    }

    /**
     * {@inheritDoc}
     */
    public function open(int $mode): FileHandle
    {
        if (isset($this->errorMessages['open'])) {
            trigger_error($this->errorMessages['open'], E_USER_WARNING);
        }

        return new ErroneousOpenedFile(parent::open($mode), $this->errorMessages);
    }

    /**
     * {@inheritDoc}
     */
    public function openForAppend(int $mode): FileHandle
    {
        if (isset($this->errorMessages['open'])) {
            trigger_error($this->errorMessages['open'], E_USER_WARNING);
        }

        return new ErroneousOpenedFile(parent::openForAppend($mode), $this->errorMessages);
    }

    /**
     * {@inheritDoc}
     */
    public function openWithTruncate(int $mode): FileHandle
    {
        if (isset($this->errorMessages['open'])) {
            trigger_error($this->errorMessages['open'], E_USER_WARNING);
        }

        return new ErroneousOpenedFile(parent::openWithTruncate($mode), $this->errorMessages);
    }

    public function lock($resource, int $operation): bool
    {
        if (isset($this->errorMessages['lock'])) {
            trigger_error($this->errorMessages['lock'], E_USER_WARNING);

            return false;
        }

        return parent::lock($resource, $operation);
    }

    public function size(): int
    {
        if (isset($this->errorMessages['stat'])) {
            trigger_error($this->errorMessages['stat'], E_USER_WARNING);

            return -1;
        }

        return parent::size();
    }

    /**
     * {@inheritDoc}
     */
    public function filemtime(): int
    {
        if (isset($this->errorMessages['stat'])) {
            trigger_error($this->errorMessages['stat'], E_USER_WARNING);

            return -1;
        }

        return parent::filemtime();
    }

    /**
     * {@inheritDoc}
     */
    public function fileatime(): int
    {
        if (isset($this->errorMessages['stat'])) {
            trigger_error($this->errorMessages['stat'], E_USER_WARNING);

            return -1;
        }

        return parent::fileatime();
    }

    /**
     * {@inheritDoc}
     */
    public function filectime(): int
    {
        if (isset($this->errorMessages['stat'])) {
            trigger_error($this->errorMessages['stat'], E_USER_WARNING);

            return -1;
        }

        return parent::filectime();
    }
}
