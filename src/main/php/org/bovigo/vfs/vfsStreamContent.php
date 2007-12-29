<?php
/**
 * Interface for stream contents.
 *
 * @author      Frank Kleine <mikey@stubbles.net>
 * @package     stubbles_vfs
 */
/**
 * Interface for stream contents.
 *
 * @package     stubbles_vfs
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
     * returns the file name of the content
     *
     * @return  string
     */
    public function getName();

    /**
     * renames the content
     *
     * @param  string  $newName
     */
    public function rename($newName);

    /**
     * checks whether the container contains a child with the given name
     *
     * @param   string  $name
     * @return  bool
     */
    public function hasChild($name);

    /**
     * checks whether the container can be applied to given name
     *
     * @param   string  $name
     * @return  bool
     */
    public function appliesTo($name);

    /**
     * returns the type of the container
     *
     * @return  int
     */
    public function getType();

    /**
     * returns size of content
     *
     * @return  int
     */
    public function size();

    /**
     * sets the last modification time of the stream content
     *
     * @param  int  $filemtime
     */
    public function setFilemtime($filemtime);

    /**
     * returns the last modification time of the stream content
     *
     * @return  int
     */
    public function filemtime();
}
?>