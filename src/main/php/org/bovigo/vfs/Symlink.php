<?php
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
 * Represents a symbolic link.
 *
 * @since  1.?.0
 */
class Symlink implements vfsStreamContent
{
    /**
     * name of the link
     *
     * @type  string
     */
    private $name;
    /**
     * target where link points to
     *
     * @type  \org\bovigo\vfs\vfsStreamContent
     */
    private $target;
    /**
     * path to to this content
     *
     * @type  string
     */
    private $parentPath;

    /**
     * constructor
     *
     * @param  string                            $name    name of link
     * @param  \org\bovigo\vfs\vfsStreamContent  $target  target where link points to
     */
    public function __construct($name, vfsStreamContent $target)
    {
        $this->name   = $name;
        $this->target = $target;
    }

    /**
     * resolves the link
     *
     * @return  \org\bovigo\vfs\vfsStreamContent
     */
    public function resolve()
    {
        return $this->target;
    }

    public function targetName()
    {
        return $this->target->getName();
    }

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
        $this->name = $newName;
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
        return vfsStreamContent::TYPE_LINK;
    }

    /**
     * returns size of content
     *
     * @return  int
     */
    public function size()
    {
        return $this->target->size();
    }

    /**
     * sets the last modification time of the stream content
     *
     * @param   int  $filemtime
     * @return  vfsStreamContent
     */
    public function lastModified($filemtime)
    {
        $this->target->lastModified($filemtime);
        return $this;
    }

    /**
     * returns the last modification time of the stream content
     *
     * @return  int
     */
    public function filemtime()
    {
        return $this->target->filemtime();
    }

    /**
     * adds content to given container
     *
     * @param   vfsStreamContainer  $container
     * @return  vfsStreamContent
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
     * @return  vfsStreamContent
     */
    public function chmod($permissions)
    {
        $this->target->chmod($permissions);
        return $this;
    }

    /**
     * returns permissions
     *
     * @return  int
     */
    public function getPermissions()
    {
        return $this->target->getPermissions();
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
        # self or target?
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
        # self or target?
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
        # self or target?
    }

    /**
     * change owner of file to given user
     *
     * @param   int  $user
     * @return  vfsStreamContent
     */
    public function chown($user)
    {
        # self or target?
    }

    /**
     * checks whether file is owned by given user
     *
     * @param   int  $user
     * @return  bool
     */
    public function isOwnedByUser($user)
    {
        # self or target?
    }

    /**
     * returns owner of file
     *
     * @return  int
     */
    public function getUser()
    {
        # self or target?
    }

    /**
     * change owner group of file to given group
     *
     * @param   int  $group
     * @return  vfsStreamContent
     */
    public function chgrp($group)
    {
        # self or target?
    }

    /**
     * checks whether file is owned by group
     *
     * @param   int   $group
     * @return  bool
     */
    public function isOwnedByGroup($group)
    {
        # self or target?
    }

    /**
     * returns owner group of file
     *
     * @return  int
     */
    public function getGroup()
    {
        # self or target?
    }

    /**
     * sets parent path
     *
     * @param  string  $parentPath
     * @internal  only to be set by parent
     */
    public function setParentPath($parentPath)
    {
        $this->parentPath = $parentPath;
    }

    /**
     * returns path to this content
     *
     * @return  string
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
     */
    public function url()
    {
        return vfsStream::url($this->path());
    }
}
