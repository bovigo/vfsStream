<?php
/**
 * Base stream contents container.
 *
 * @package     bovigo_vfs
 * @version     $Id$
 */
/**
 * @ignore
 */
require_once dirname(__FILE__) . '/vfsStreamContent.php';
/**
 * Base stream contents container.
 *
 * @package     bovigo_vfs
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
     * @var  string
     */
    protected $type;
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
     * constructor
     *
     * @param  string  $name
     * @param  int     $permissions
     */
    public function __construct($name, $permissions = 0777)
    {
        $this->name         = $name;
        $this->lastModified = time();
        $this->permissions  = $permissions;
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
        return (substr($name, 0, strlen($this->name)) === $this->name);
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
     * alias for lastModified()
     *
     * @param   int               $filemtime
     * @return  vfsStreamContent
     * @see     lastModified()
     */
    public function setFilemtime($filemtime)
    {
        return $this->lastModified($filemtime);
    }

    /**
     * sets the last modification time of the stream content
     *
     * @param   int               $filemtime
     * @return  vfsStreamContent
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
     * @param   int               $permissions
     * @return  vfsStreamContent
     */
    public function chmod($permissions)
    {
        $this->permissions = $permissions;
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
}
?>