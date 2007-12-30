<?php
/**
 * Interface for stream contents.
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo_vfs
 */
/**
 * @ignore
 */
require_once dirname(__FILE__) . '/vfsStreamContainer.php';
/**
 * Interface for stream contents.
 *
 * @package     bovigo_vfs
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
     * alias for lastModified()
     *
     * @param   int               $filemtime
     * @return  vfsStreamContent
     * @see     lastModified()
     */
    public function setFilemtime($filemtime);

    /**
     * sets the last modification time of the stream content
     *
     * @param   int               $filemtime
     * @return  vfsStreamContent
     */
    public function lastModified($filemtime);

    /**
     * returns the last modification time of the stream content
     *
     * @return  int
     */
    public function filemtime();

    /**
     * adds content to given container
     *
     * @param   vfsStreamContainer  $container
     * @return  vfsStreamContent
     */
    public function at(vfsStreamContainer $container);
}
?>