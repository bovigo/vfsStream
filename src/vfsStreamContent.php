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

/**
 * Interface for stream contents.
 */
interface vfsStreamContent
{
    /**
     * stream content type: file
     *
     * @see  getType()
     */
    public const TYPE_FILE = 0100000;
    /**
     * stream content type: directory
     *
     * @see  getType()
     */
    public const TYPE_DIR = 0040000;
    /**
     * stream content type: symbolic link
     *
     * @see  getType();
     */
    // const TYPE_LINK = 0120000;

    /**
     * stream content type: block
     *
     * @see getType()
     */
    public const TYPE_BLOCK = 0060000;

    /**
     * returns the file name of the content
     */
    public function getName(): string;

    /**
     * renames the content
     */
    public function rename(string $newName): void;

    /**
     * checks whether the container can be applied to given name
     */
    public function appliesTo(string $name): bool;

    /**
     * returns the type of the container
     */
    public function getType(): int;

    /**
     * returns size of content
     */
    public function size(): int;

    /**
     * sets the last modification time of the stream content
     */
    public function lastModified(int $filemtime): self;

    /**
     * returns the last modification time of the stream content
     */
    public function filemtime(): int;

    /**
     * adds content to given container
     */
    public function at(vfsStreamContainer $container): self;

    /**
     * change file mode to given permissions
     */
    public function chmod(int $permissions): self;

    /**
     * returns permissions
     */
    public function getPermissions(): int;

    /**
     * checks whether content is readable
     *
     * @param   int $user  id of user to check for
     * @param   int $group id of group to check for
     */
    public function isReadable(int $user, int $group): bool;

    /**
     * checks whether content is writable
     *
     * @param   int $user  id of user to check for
     * @param   int $group id of group to check for
     */
    public function isWritable(int $user, int $group): bool;

    /**
     * checks whether content is executable
     *
     * @param   int $user  id of user to check for
     * @param   int $group id of group to check for
     */
    public function isExecutable(int $user, int $group): bool;

    /**
     * change owner of file to given user
     */
    public function chown(int $user): self;

    /**
     * checks whether file is owned by given user
     */
    public function isOwnedByUser(int $user): bool;

    /**
     * returns owner of file
     */
    public function getUser(): int;

    /**
     * change owner group of file to given group
     */
    public function chgrp(int $group): self;

    /**
     * checks whether file is owned by group
     */
    public function isOwnedByGroup(int $group): bool;

    /**
     * returns owner group of file
     */
    public function getGroup(): int;

    /**
     * sets parent path
     *
     * @internal  only to be set by parent
     *
     * @since   1.2.0
     */
    public function setParentPath(string $parentPath): void;

    /**
     * removes parent path
     *
     * @internal  only to be set by parent
     *
     * @since   2.0.0
     */
    public function removeParentPath(): void;

    /**
     * returns path to this content
     *
     * @since   1.2.0
     */
    public function path(): string;

    /**
     * returns complete vfsStream url for this content
     *
     * @since   1.2.0
     */
    public function url(): string;
}

class_alias('bovigo\vfs\vfsStreamContent', 'org\bovigo\vfs\vfsStreamContent');
