<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
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
abstract class BasicFile implements vfsStreamContent
{
    /**
     * name of the container
     *
     * @type  string
     */
    protected $name;
    /**
     * type of the container
     *
     * @type  string
     */
    protected $type;
    /**
     * timestamp of last access
     *
     * @type  int
     */
    protected $lastAccessed;
    /**
     * timestamp of last attribute modification
     *
     * @type  int
     */
    protected $lastAttributeModified;
    /**
     * timestamp of last modification
     *
     * @type  int
     */
    protected $lastModified;
    /**
     * permissions for content
     *
     * @type  int
     */
    protected $permissions;
    /**
     * owner of the file
     *
     * @type  int
     */
    protected $user;
    /**
     * owner group of the file
     *
     * @type  int
     */
    protected $group;
    /**
     * path to to this content
     *
     * @type  string
     */
    private $parentPath;

    /**
     * constructor
     *
     * @param  string  $name
     * @param  int     $permissions  optional
     */
    public function __construct($name, $permissions = null)
    {
        $this->name = "{$name}";
        $time       = time();
        if (null === $permissions) {
            $permissions = $this->getDefaultPermissions() & ~vfsStream::umask();
        }

        $this->lastAccessed          = $time;
        $this->lastAttributeModified = $time;
        $this->lastModified          = $time;
        $this->permissions           = $permissions;
        $this->user                  = vfsStream::getCurrentUser();
        $this->group                 = vfsStream::getCurrentGroup();
    }

    /**
     * returns default permissions for concrete implementation
     *
     * @return  int
     * @since   0.8.0
     */
    protected abstract function getDefaultPermissions();

    /**
     * returns the file name of the content
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * renames the content
     *
     * @param  string  $newName
     */
    public function rename($newName)
    {
        $this->name = "{$newName}";
    }

    /**
     * checks whether the container can be applied to given name
     *
     * @param   string  $name
     * @return  bool
     */
    public function appliesTo($name)
    {
        if ($name === $this->name) {
            return true;
        }

        $segment_name = $this->name.'/';
        return (strncmp($segment_name, $name, strlen($segment_name)) == 0);
    }

    /**
     * returns the type of the container
     *
     * @return  int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * sets the last modification time of the stream content
     *
     * @param   int  $filemtime
     * @return  $this
     */
    public function lastModified($filemtime)
    {
        $this->lastModified = $filemtime;
        return $this;
    }

    /**
     * returns the last modification time of the stream content
     *
     * @return  int
     */
    public function filemtime()
    {
        return $this->lastModified;
    }

    /**
     * sets last access time of the stream content
     *
     * @param   int  $fileatime
     * @return  $this
     * @since   0.9
     */
    public function lastAccessed($fileatime)
    {
        $this->lastAccessed = $fileatime;
        return $this;
    }

    /**
     * returns the last access time of the stream content
     *
     * @return  int
     * @since   0.9
     */
    public function fileatime()
    {
        return $this->lastAccessed;
    }

    /**
     * sets the last attribute modification time of the stream content
     *
     * @param   int  $filectime
     * @return  $this
     * @since   0.9
     */
    public function lastAttributeModified($filectime)
    {
        $this->lastAttributeModified = $filectime;
        return $this;
    }

    /**
     * returns the last attribute modification time of the stream content
     *
     * @return  int
     * @since   0.9
     */
    public function filectime()
    {
        return $this->lastAttributeModified;
    }

    /**
     * adds content to given container
     *
     * @param   vfsStreamContainer  $container
     * @return  $this
     */
    public function at(vfsStreamContainer $container)
    {
        $container->addChild($this);
        return $this;
    }

    /**
     * change file mode to given permissions
     *
     * @param   int  $permissions
     * @return  $this
     */
    public function chmod($permissions)
    {
        $this->permissions           = $permissions;
        $this->lastAttributeModified = time();
        clearstatcache();
        return $this;
    }

    /**
     * returns permissions
     *
     * @return  int
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * checks whether content is readable
     *
     * @param   int   $user   id of user to check for
     * @param   int   $group  id of group to check for
     * @return  bool
     */
    public function isReadable($user, $group)
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
     * @param   int   $user   id of user to check for
     * @param   int   $group  id of group to check for
     * @return  bool
     */
    public function isWritable($user, $group)
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
     * @param   int   $user   id of user to check for
     * @param   int   $group  id of group to check for
     * @return  bool
     */
    public function isExecutable($user, $group)
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
     * @param   int  $user
     * @return  $this
     */
    public function chown($user)
    {
        $this->user                  = $user;
        $this->lastAttributeModified = time();
        return $this;
    }

    /**
     * checks whether file is owned by given user
     *
     * @param   int  $user
     * @return  bool
     */
    public function isOwnedByUser($user)
    {
        return $this->user === $user;
    }

    /**
     * returns owner of file
     *
     * @return  int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * change owner group of file to given group
     *
     * @param   int  $group
     * @return  $this
     */
    public function chgrp($group)
    {
        $this->group                 = $group;
        $this->lastAttributeModified = time();
        return $this;
    }

    /**
     * checks whether file is owned by group
     *
     * @param   int   $group
     * @return  bool
     */
    public function isOwnedByGroup($group)
    {
        return $this->group === $group;
    }

    /**
     * returns owner group of file
     *
     * @return  int
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * sets parent path
     *
     * @param  string  $parentPath
     * @internal  only to be set by parent
     * @since   1.2.0
     */
    public function setParentPath($parentPath)
    {
        $this->parentPath = $parentPath;
    }

    /**
     * returns path to this content
     *
     * @return  string
     * @since   1.2.0
     */
    public function path()
    {
        if (null === $this->parentPath) {
            return $this->name;
        }

        return $this->parentPath . '/' . $this->name;
    }

    /**
     * returns complete vfsStream url for this content
     *
     * @return  string
     * @since   1.2.0
     */
    public function url()
    {
        return vfsStream::url($this->path());
    }
}

class_alias('bovigo\vfs\BasicFile', 'org\bovigo\vfs\vfsStreamAbstractContent');
