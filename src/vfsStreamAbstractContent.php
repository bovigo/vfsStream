<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs;

use function class_alias;
use function clearstatcache;
use function strlen;
use function strncmp;
use function strstr;
use function time;

/**
 * Base stream contents container.
 */
abstract class vfsStreamAbstractContent implements vfsStreamContent
{
    /**
     * name of the container
     *
     * @var  string
     */
    protected $name;
    /**
     * type of the container
     *
     * @var  int
     */
    protected $type;
    /**
     * timestamp of last access
     *
     * @var  int
     */
    protected $lastAccessed;
    /**
     * timestamp of last attribute modification
     *
     * @var  int
     */
    protected $lastAttributeModified;
    /**
     * timestamp of last modification
     *
     * @var  int
     */
    protected $lastModified;
    /**
     * permissions for content
     *
     * @var  int
     */
    protected $permissions;
    /**
     * owner of the file
     *
     * @var  int
     */
    protected $user;
    /**
     * owner group of the file
     *
     * @var  int
     */
    protected $group;
    /**
     * path to to this content
     *
     * @var  string|null
     */
    private $parentPath;

    /**
     * constructor
     *
     * @param  int|null $permissions optional
     */
    public function __construct(string $name, ?int $permissions = null)
    {
        if (strstr($name, '/') !== false) {
            throw new vfsStreamException('Name can not contain /.');
        }

        $this->name = $name;
        $time = time();
        if ($permissions === null) {
            $permissions = $this->getDefaultPermissions() & ~vfsStream::umask();
        }

        $this->lastAccessed = $time;
        $this->lastAttributeModified = $time;
        $this->lastModified = $time;
        $this->permissions = $permissions;
        $this->user = vfsStream::getCurrentUser();
        $this->group = vfsStream::getCurrentGroup();
    }

    /**
     * returns default permissions for concrete implementation
     *
     * @since   0.8.0
     */
    abstract protected function getDefaultPermissions(): int;

    /**
     * returns the file name of the content
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * renames the content
     */
    public function rename(string $newName): void
    {
        if (strstr($newName, '/') !== false) {
            throw new vfsStreamException('Name can not contain /.');
        }

        $this->name = $newName;
    }

    /**
     * checks whether the container can be applied to given name
     */
    public function appliesTo(string $name): bool
    {
        if ($name === $this->name) {
            return true;
        }

        $segment_name = $this->name . '/';

        return strncmp($segment_name, $name, strlen($segment_name)) === 0;
    }

    /**
     * returns the type of the container
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * sets the last modification time of the stream content
     */
    public function lastModified(int $filemtime): vfsStreamContent
    {
        $this->lastModified = $filemtime;

        return $this;
    }

    /**
     * returns the last modification time of the stream content
     */
    public function filemtime(): int
    {
        return $this->lastModified;
    }

    /**
     * sets last access time of the stream content
     *
     * @since   0.9
     */
    public function lastAccessed(int $fileatime): vfsStreamContent
    {
        $this->lastAccessed = $fileatime;

        return $this;
    }

    /**
     * returns the last access time of the stream content
     *
     * @since   0.9
     */
    public function fileatime(): int
    {
        return $this->lastAccessed;
    }

    /**
     * sets the last attribute modification time of the stream content
     *
     * @since   0.9
     */
    public function lastAttributeModified(int $filectime): vfsStreamContent
    {
        $this->lastAttributeModified = $filectime;

        return $this;
    }

    /**
     * returns the last attribute modification time of the stream content
     *
     * @since   0.9
     */
    public function filectime(): int
    {
        return $this->lastAttributeModified;
    }

    /**
     * adds content to given container
     */
    public function at(vfsStreamContainer $container): vfsStreamContent
    {
        $container->addChild($this);

        return $this;
    }

    /**
     * change file mode to given permissions
     */
    public function chmod(int $permissions): vfsStreamContent
    {
        $this->permissions = $permissions;
        $this->lastAttributeModified = time();
        clearstatcache();

        return $this;
    }

    /**
     * returns permissions
     */
    public function getPermissions(): int
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
     */
    public function chown(int $user): vfsStreamContent
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
     * returns owner of file
     */
    public function getUser(): int
    {
        return $this->user;
    }

    /**
     * change owner group of file to given group
     */
    public function chgrp(int $group): vfsStreamContent
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
     * returns owner group of file
     */
    public function getGroup(): int
    {
        return $this->group;
    }

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
     * @since   1.2.0
     */
    public function url(): string
    {
        return vfsStream::url($this->path());
    }
}

class_alias('bovigo\vfs\vfsStreamAbstractContent', 'org\bovigo\vfs\vfsStreamAbstractContent');
