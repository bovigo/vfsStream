<?php
/**
 * Base stream contents container.
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo_vfs
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
     * time of last modification
     *
     * @var  int
     */
    protected $filemtime;

    /**
     * constructor
     *
     * @param  string  $name
     */
    public function __construct($name)
    {
        $this->name      = $name;
        $this->filemtime = time();
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
     * sets the last modification time of the stream content
     *
     * @param  int  $filemtime
     */
    public function setFilemtime($filemtime)
    {
        $this->filemtime = $filemtime;
        return $this;
    }

    /**
     * returns the last modification time of the stream content
     *
     * @return  int
     */
    public function filemtime()
    {
        return $this->filemtime;
    }
}
?>