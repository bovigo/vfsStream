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

/**
 * Represents a basic entry in the file system.
 *
 * Due to simplicity reasons it extends Inode, even though that is not a correct
 * implementation if when looked from a domain point of view.
 *
 * @internal
 */
abstract class BasicFile extends Inode
{
    /**
     * @var  string
     */
    private $name;
    /**
    * path to to this file
    *
    * @var  string|null
    */
   private $parentPath;

    public function __construct(string $name, int $permissions)
    {
        $this->check($name);
        $this->name = $name;
        parent::__construct($permissions);
    }

    private function check(string $name)
    {
        if (strstr($name, '/') !== false) {
            throw new vfsStreamException('Name can not contain /.');
        }
    }

    /**
     * renames the file
     */
    public function rename(string $newName): void
    {
        $this->check($newName);
        $this->name = $newName;
    }

    /**
     * @deprecated  use name() instead
     */
    public function getName(): string
    {
        return $this->name();
    }

    /**
     * @api
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * checks whether the file can be applied to given name
     */
    public function appliesTo(string $name): bool
    {
        return $this->name === $name;
    }

    /**
     * @deprecated  use type() instead
     */
    public function getType(): int
    {
        return $this->type();
    }

    /**
     * returns the type of the file
     */
    abstract public function type(): int;

    /**
     * returns size of content
     */
    abstract public function size(): int;

    /**
     * sets parent path
     *
     * @internal  only to be set by parent
     *
     * @since   1.2.0
     */
    public function setParentPath(string $parentPath): void
    {
        $this->parentPath = $parentPath;
    }

    /**
     * removes parent path
     *
     * @internal  only to be set by parent
     *
     * @since   2.0.0
     */
    public function removeParentPath(): void
    {
        $this->parentPath = null;
    }

    /**
     * returns path to this content
     *
     * @api
     * @since   1.2.0
     */
    public function path(): string
    {
        if ($this->parentPath === null) {
            return $this->name;
        }

        return $this->parentPath . '/' . $this->name;
    }

    /**
     * returns complete vfsStream url for this content
     *
     * @api
     * @since   1.2.0
     */
    public function url(): string
    {
        return vfsStream::url($this->path());
    }

    /**
     * returns status of file
     *
     * @return int[]|false
     */
    public function stat()
    {
        $atime = $this->fileatime();
        $ctime = $this->filectime();
        $mtime = $this->filemtime();
        $size = $this->size();
        if ($atime === -1 || $ctime === -1 || $mtime === -1 || $size === -1) {
            return false;
        }

        $fileStat = [
            'dev' => 0,
            'ino' => spl_object_id($this),
            'mode' => $this->type() | $this->permissions(),
            'nlink' => 0,
            'uid' => $this->user(),
            'gid' => $this->group(),
            'rdev' => 0,
            'size' => $size,
            'atime' => $atime,
            'mtime' => $mtime,
            'ctime' => $ctime,
            'blksize' => -1,
            'blocks' => -1,
        ];

        return array_merge(array_values($fileStat), $fileStat);
    }
}