<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs;

use function clearstatcache;
use function time;

/**
 * Wraps all metadata about a file.
 * 
 * @internal
 */
class Inode
{
    /**
     * timestamp of last access
     *
     * @var  int
     */
    private $lastAccessed;
    /**
     * timestamp of last attribute modification
     *
     * @var  int
     */
    private $lastAttributeModified;
    /**
     * timestamp of last modification
     *
     * @var  int
     */
    private $lastModified;
    /**
     * permissions for content
     *
     * @var  int
     */
    private $permissions;
    /**
     * owner of the file
     *
     * @var  int
     */
    private $user;
    /**
     * owner group of the file
     *
     * @var  int
     */
    private $group;

    /**
     * constructor
     *
     * @param  int  $permissions
     */
    public function __construct(int $permissions)
    {
        $time = time();
        $this->lastAccessed = $time;
        $this->lastAttributeModified = $time;
        $this->lastModified = $time;
        $this->permissions = $permissions;
        $this->user = vfsStream::getCurrentUser();
        $this->group = vfsStream::getCurrentGroup();
    }

    /**
     * sets the last modification time of the stream content
     * 
     * @api
     */
    public function lastModified(int $filemtime): self
    {
        $this->lastModified = $filemtime;

        return $this;
    }

    /**
     * returns the last modification time of the stream content
     * 
     * @api
     */
    public function filemtime(): int
    {
        return $this->lastModified;
    }

    /**
     * sets last access time of the stream content
     *
     * @api
     * @since   0.9
     */
    public function lastAccessed(int $fileatime): self
    {
        $this->lastAccessed = $fileatime;

        return $this;
    }

    /**
     * returns the last access time of the stream content
     *
     * @api
     * @since   0.9
     */
    public function fileatime(): int
    {
        return $this->lastAccessed;
    }

    /**
     * sets the last attribute modification time of the stream content
     *
     * @api
     * @since   0.9
     */
    public function lastAttributeModified(int $filectime): self
    {
        $this->lastAttributeModified = $filectime;

        return $this;
    }

    /**
     * returns the last attribute modification time of the stream content
     *
     * @api
     * @since   0.9
     */
    public function filectime(): int
    {
        return $this->lastAttributeModified;
    }

    /**
     * updates internal timestamps
     */
    public function updateModifications(): void
    {
        $time = time();
        $this->lastAttributeModified = $time;
        $this->lastModified = $time;
    }

    /**
     * change file mode to given permissions
     * 
     * @api
     */
    public function chmod(int $permissions): self
    {
        $this->permissions = $permissions;
        $this->lastAttributeModified = time();
        clearstatcache();

        return $this;
    }

    /**
     * @deprecated  use permissions() instead
     */
    public function getPermissions(): int
    {
        return $this->permissions();
    }

    /**
     * returns permissions
     * 
     * @api
     */
    public function permissions(): int
    {
        return $this->permissions;
    }

    /**
     * checks whether content is readable
     *
     * @param   int $user  id of user to check for
     * @param   int $group id of group to check for
     */
    public function isReadable(int $user, int $group): bool
    {
        if ($this->user === $user) {
            $check = 0400;
        } elseif ($this->group === $group) {
            $check = 0040;
        } else {
            $check = 0004;
        }

        return (bool) ($this->permissions & $check);
    }

    /**
     * checks whether content is writable
     *
     * @param   int $user  id of user to check for
     * @param   int $group id of group to check for
     */
    public function isWritable(int $user, int $group): bool
    {
        if ($this->user === $user) {
            $check = 0200;
        } elseif ($this->group === $group) {
            $check = 0020;
        } else {
            $check = 0002;
        }

        return (bool) ($this->permissions & $check);
    }

    /**
     * checks whether content is executable
     *
     * @param   int $user  id of user to check for
     * @param   int $group id of group to check for
     */
    public function isExecutable(int $user, int $group): bool
    {
        if ($this->user === $user) {
            $check = 0100;
        } elseif ($this->group === $group) {
            $check = 0010;
        } else {
            $check = 0001;
        }

        return (bool) ($this->permissions & $check);
    }

    /**
     * change owner of file to given user
     * 
     * @api
     */
    public function chown(int $user): self
    {
        $this->user = $user;
        $this->lastAttributeModified = time();

        return $this;
    }

    /**
     * checks whether file is owned by given user
     */
    public function isOwnedByUser(int $user): bool
    {
        return $this->user === $user;
    }

    /**
     * @deprecated  use user() instead
     */
    public function getUser(): int
    {
        return $this->user();
    }

    /**
     * returns owner of file
     */
    public function user(): int
    {
        return $this->user;
    }

    /**
     * change owner group of file to given group
     * 
     * @api
     */
    public function chgrp(int $group): self
    {
        $this->group = $group;
        $this->lastAttributeModified = time();

        return $this;
    }

    /**
     * checks whether file is owned by group
     */
    public function isOwnedByGroup(int $group): bool
    {
        return $this->group === $group;
    }

    /**
     * @deprecated  use group() instead
     */
    public function getGroup(): int
    {
        return $this->group();
    }

    /**
     * returns owner group of file
     */
    public function group(): int
    {
        return $this->group;
    }
}