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

namespace bovigo\vfs;

use bovigo\vfs\content\FileContent;
use bovigo\vfs\content\StringBasedFileContent;
use bovigo\vfs\internal\FileHandle;
use bovigo\vfs\internal\OpenedFile;
use bovigo\vfs\internal\Type;
use InvalidArgumentException;
use const LOCK_EX;
use const LOCK_NB;
use const LOCK_SH;
use function class_alias;
use function is_resource;
use function is_string;
use function spl_object_hash;
use function sprintf;
use function stream_get_meta_data;
use function time;

/**
 * File container.
 *
 * @api
 */
class vfsFile extends BasicFile
{
    /**
     * content of the file
     *
     * @var  FileContent
     */
    private $content;
    /**
     * Resource id which exclusively locked this file
     *
     * @var  string|null
     */
    private $exclusiveLock;
    /**
     * Resources ids which currently holds shared lock to this file
     *
     * @var  array<string, bool>
     */
    private $sharedLock = [];
    /**
     * default file permissions
     */
    public const DEFAULT_PERMISSIONS = 0666;

    /**
     * constructor
     *
     * @param int|null $permissions optional
     */
    public function __construct(string $name, ?int $permissions = null)
    {
        parent::__construct($name, $permissions ?? (self::DEFAULT_PERMISSIONS & ~vfsStream::umask()));
        $this->content = new StringBasedFileContent('');
    }

    /**
     * returns the type of the file
     */
    public function type(): int
    {
        return Type::FILE;
    }

    /**
     * returns size of content
     */
    public function size(): int
    {
        return $this->content->size();
    }

    /**
     * adds file to given directory
     */
    public function at(vfsDirectory $directory): self
    {
        $directory->addChild($this);

        return $this;
    }

    /**
     * alias for withContent()
     *
     * @see     withContent()
     *
     * @param string|FileContent $content
     */
    public function setContent($content): self
    {
        return $this->withContent($content);
    }

    /**
     * sets the contents of the file
     *
     * Setting content with this method does not change the time when the file
     * was last modified.
     *
     * @param string|FileContent $content
     *
     * @throws InvalidArgumentException
     */
    public function withContent($content): self
    {
        if (is_string($content)) {
            $this->content = new StringBasedFileContent($content);
        } elseif ($content instanceof FileContent) {
            $this->content = $content;
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'Given content must either be a string or an instance of %s',
                    FileContent::class
                )
            );
        }

        return $this;
    }

    /**
     * @deprecated  use content() instead
     */
    public function getContent(): string
    {
        return $this->content();
    }

    public function content(): string
    {
        return $this->content->content();
    }

    /**
     * simply open the file
     *
     * @internal
     *
     * @since  0.9
     */
    public function open(int $mode): FileHandle
    {
        $this->lastAccessed(time());

        return new OpenedFile($this, $this->content, $mode);
    }

    /**
     * open file and set pointer to end of file
     *
     * @internal
     *
     * @since  0.9
     */
    public function openForAppend(int $mode): FileHandle
    {
        $this->lastAccessed(time());

        return OpenedFile::append($this, $this->content, $mode);
    }

    /**
     * open file and truncate content
     *
     * @internal
     *
     * @since  0.9
     */
    public function openWithTruncate(int $mode): FileHandle
    {
        $this->content->truncate(0);
        $time = time();
        $this->lastAccessed($time);
        $this->lastModified($time);

        return new OpenedFile($this, $this->content, $mode);
    }

    /**
     * locks file for
     *
     * @see     https://github.com/mikey179/vfsStream/issues/6
     * @see     https://github.com/mikey179/vfsStream/issues/40
     *
     * @param resource|StreamWrapper $resource
     *
     * @since   0.10.0
     */
    public function lock($resource, int $operation): bool
    {
        if ((LOCK_NB & $operation) === LOCK_NB) {
            $operation -= LOCK_NB;
        }

        // call to lock file on the same file handler firstly releases the lock
        $this->unlock($resource);

        if ($operation === LOCK_EX) {
            if ($this->isLocked()) {
                return false;
            }

            $this->setExclusiveLock($resource);
        } elseif ($operation === LOCK_SH) {
            if ($this->hasExclusiveLock()) {
                return false;
            }

            $this->addSharedLock($resource);
        }

        return true;
    }

    /**
     * Removes lock from file acquired by given resource
     *
     * @see     https://github.com/mikey179/vfsStream/issues/40
     *
     * @param resource|StreamWrapper $resource
     */
    public function unlock($resource): void
    {
        if ($this->hasExclusiveLock($resource)) {
            $this->exclusiveLock = null;
        }
        if (! $this->hasSharedLock($resource)) {
            return;
        }

        unset($this->sharedLock[$this->resourceId($resource)]);
    }

    /**
     * Set exlusive lock on file by given resource
     *
     * @see     https://github.com/mikey179/vfsStream/issues/40
     *
     * @param resource|StreamWrapper $resource
     */
    protected function setExclusiveLock($resource): void
    {
        $this->exclusiveLock = $this->resourceId($resource);
    }

    /**
     * Add shared lock on file by given resource
     *
     * @see     https://github.com/mikey179/vfsStream/issues/40
     *
     * @param resource|StreamWrapper $resource
     */
    protected function addSharedLock($resource): void
    {
        $this->sharedLock[$this->resourceId($resource)] = true;
    }

    /**
     * checks whether file is locked
     *
     * @see     https://github.com/mikey179/vfsStream/issues/6
     * @see     https://github.com/mikey179/vfsStream/issues/40
     *
     * @param resource|StreamWrapper $resource
     *
     * @since   0.10.0
     */
    public function isLocked($resource = null): bool
    {
        return $this->hasSharedLock($resource) || $this->hasExclusiveLock($resource);
    }

    /**
     * checks whether file is locked in shared mode
     *
     * @see     https://github.com/mikey179/vfsStream/issues/6
     * @see     https://github.com/mikey179/vfsStream/issues/40
     *
     * @param resource|StreamWrapper $resource
     *
     * @since   0.10.0
     */
    public function hasSharedLock($resource = null): bool
    {
        if ($resource !== null) {
            return isset($this->sharedLock[$this->resourceId($resource)]);
        }

        return ! empty($this->sharedLock);
    }

    /**
     * Returns unique resource id
     *
     * @see     https://github.com/mikey179/vfsStream/issues/40
     *
     * @param resource|StreamWrapper $resource
     */
    private function resourceId($resource): string
    {
        if (is_resource($resource)) {
            $data = stream_get_meta_data($resource);
            $resource = $data['wrapper_data'];
        }

        return spl_object_hash($resource);
    }

    /**
     * checks whether file is locked in exclusive mode
     *
     * @see     https://github.com/mikey179/vfsStream/issues/6
     * @see     https://github.com/mikey179/vfsStream/issues/40
     *
     * @param resource|StreamWrapper $resource
     *
     * @since   0.10.0
     */
    public function hasExclusiveLock($resource = null): bool
    {
        if ($resource !== null) {
            return $this->exclusiveLock === $this->resourceId($resource);
        }

        return $this->exclusiveLock !== null;
    }
}
class_alias('bovigo\vfs\vfsFile', 'org\bovigo\vfs\vfsStreamFile');
