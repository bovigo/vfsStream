<?php
declare(strict_types=1);
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
namespace org\bovigo\vfs;
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
    const TYPE_FILE = 0100000;
    /**
     * stream content type: directory
     *
     * @see  getType()
     */
    const TYPE_DIR  = 0040000;
    /**
     * stream content type: symbolic link
     *
     * @see  getType();
     */
    #const TYPE_LINK = 0120000;

    /**
     * stream content type: block
     *
     * @see getType()
     */
    const TYPE_BLOCK = 0060000;

    /**
     * returns the file name of the content
     *
     * @return  string
     */
    public function getName(): string;

    /**
     * renames the content
     *
     * @param  string  $newName
     */
    public function rename(string $newName);

    /**
     * checks whether the container can be applied to given name
     *
     * @param   string  $name
     * @return  bool
     */
    public function appliesTo(string $name): bool;

    /**
     * returns the type of the container
     *
     * @return  int
     */
    public function getType(): int;

    /**
     * returns size of content
     *
     * @return  int
     */
    public function size(): int;

    /**
     * sets the last modification time of the stream content
     *
     * @param   int  $filemtime
     * @return  vfsStreamContent
     */
    public function lastModified(int $filemtime): self;

    /**
     * returns the last modification time of the stream content
     *
     * @return  int
     */
    public function filemtime(): int;

    /**
     * adds content to given container
     *
     * @param   vfsStreamContainer  $container
     * @return  vfsStreamContent
     */
    public function at(vfsStreamContainer $container): self;

    /**
     * change file mode to given permissions
     *
     * @param   int  $permissions
     * @return  vfsStreamContent
     */
    public function chmod(int $permissions): self;

    /**
     * returns permissions
     *
     * @return  int
     */
    public function getPermissions(): int;

    /**
     * checks whether content is readable
     *
     * @param   int   $user   id of user to check for
     * @param   int   $group  id of group to check for
     * @return  bool
     */
    public function isReadable(int $user, int $group): bool;

    /**
     * checks whether content is writable
     *
     * @param   int   $user   id of user to check for
     * @param   int   $group  id of group to check for
     * @return  bool
     */
    public function isWritable(int $user, int $group): bool;

    /**
     * checks whether content is executable
     *
     * @param   int   $user   id of user to check for
     * @param   int   $group  id of group to check for
     * @return  bool
     */
    public function isExecutable(int $user, int $group): bool;

    /**
     * change owner of file to given user
     *
     * @param   int  $user
     * @return  vfsStreamContent
     */
    public function chown(int $user): self;

    /**
     * checks whether file is owned by given user
     *
     * @param   int  $user
     * @return  bool
     */
    public function isOwnedByUser(int $user): bool;

    /**
     * returns owner of file
     *
     * @return  int
     */
    public function getUser(): int;

    /**
     * change owner group of file to given group
     *
     * @param   int  $group
     * @return  vfsStreamContent
     */
    public function chgrp(int $group): self;

    /**
     * checks whether file is owned by group
     *
     * @param   int   $group
     * @return  bool
     */
    public function isOwnedByGroup(int $group): bool;

    /**
     * returns owner group of file
     *
     * @return  int
     */
    public function getGroup(): int;

    /**
     * sets parent path
     *
     * @param  string  $parentPath
     * @internal  only to be set by parent
     * @since   1.2.0
     */
    public function setParentPath(string $parentPath);

    /**
     * removes parent path
     *
     * @internal  only to be set by parent
     * @since   2.0.0
     */
    public function removeParentPath();

    /**
     * returns path to this content
     *
     * @return  string
     * @since   1.2.0
     */
    public function path(): string;

    /**
     * returns complete vfsStream url for this content
     *
     * @return  string
     * @since   1.2.0
     */
    public function url(): string;
}
