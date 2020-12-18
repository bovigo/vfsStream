<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs;

use function trigger_error;

use const E_USER_WARNING;

/**
 * File to trigger errors on specific actions.
 *
 * Allows for throwing an error during fopen, fwrite, etc.
 *
 * @api
 */
class vfsStreamErroneousFile extends vfsStreamFile
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

    public function open(): void
    {
        if (isset($this->errorMessages['open'])) {
            trigger_error($this->errorMessages['open'], E_USER_WARNING);

            return;
        }

        parent::open();
    }

    public function openForAppend(): void
    {
        if (isset($this->errorMessages['open'])) {
            trigger_error($this->errorMessages['open'], E_USER_WARNING);

            return;
        }

        parent::openForAppend();
    }

    public function openWithTruncate(): void
    {
        if (isset($this->errorMessages['open'])) {
            trigger_error($this->errorMessages['open'], E_USER_WARNING);

            return;
        }

        parent::openWithTruncate();
    }

    public function read(int $count): string
    {
        if (isset($this->errorMessages['read'])) {
            trigger_error($this->errorMessages['read'], E_USER_WARNING);

            return '';
        }

        return parent::read($count);
    }

    public function readUntilEnd(): string
    {
        if (isset($this->errorMessages['read'])) {
            trigger_error($this->errorMessages['read'], E_USER_WARNING);

            return '';
        }

        return parent::readUntilEnd();
    }

    public function write(string $data): int
    {
        if (isset($this->errorMessages['write'])) {
            trigger_error($this->errorMessages['write'], E_USER_WARNING);

            return 0;
        }

        return parent::write($data);
    }

    public function truncate(int $size): bool
    {
        if (isset($this->errorMessages['truncate'])) {
            trigger_error($this->errorMessages['truncate'], E_USER_WARNING);

            return false;
        }

        return parent::truncate($size);
    }

    public function eof(): bool
    {
        if (isset($this->errorMessages['eof'])) {
            trigger_error($this->errorMessages['eof'], E_USER_WARNING);

            // True on error.
            // See: https://www.php.net/manual/en/function.feof.php#refsect1-function.feof-returnvalues
            return true;
        }

        return parent::eof();
    }

    public function getBytesRead(): int
    {
        if (isset($this->errorMessages['tell'])) {
            trigger_error($this->errorMessages['tell'], E_USER_WARNING);

            return 0;
        }

        return parent::getBytesRead();
    }

    public function seek(int $offset, int $whence): bool
    {
        if (isset($this->errorMessages['seek'])) {
            trigger_error($this->errorMessages['seek'], E_USER_WARNING);

            return false;
        }

        return parent::seek($offset, $whence);
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
    public function lock($resource, int $operation): bool
    {
        if (isset($this->errorMessages['lock'])) {
            trigger_error($this->errorMessages['lock'], E_USER_WARNING);

            return false;
        }

        return parent::lock($resource, $operation);
    }

    public function filemtime(): int
    {
        if (isset($this->errorMessages['stat'])) {
            trigger_error($this->errorMessages['stat'], E_USER_WARNING);

            return -1;
        }

        return parent::filemtime();
    }

    public function fileatime(): int
    {
        if (isset($this->errorMessages['stat'])) {
            trigger_error($this->errorMessages['stat'], E_USER_WARNING);

            return -1;
        }

        return parent::fileatime();
    }

    public function filectime(): int
    {
        if (isset($this->errorMessages['stat'])) {
            trigger_error($this->errorMessages['stat'], E_USER_WARNING);

            return -1;
        }

        return parent::filectime();
    }
}
