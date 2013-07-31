<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

/**
 * Interface for stream contents that are able to store other stream contents.
 */
interface vfsStream_Interface_Container extends IteratorAggregate
{
    /**
     * adds child to the directory
     *
     * @param  vfsStream_Interface_Content  $child
     */
    public function addChild(vfsStream_Interface_Content $child);

    /**
     * removes child from the directory
     *
     * @param   string  $name
     * @return  bool
     */
    public function removeChild($name);

    /**
     * checks whether the container contains a child with the given name
     *
     * @param   string  $name
     * @return  bool
     */
    public function hasChild($name);

    /**
     * returns the child with the given name
     *
     * @param   string  $name
     * @return  vfsStream_Interface_Content
     */
    public function getChild($name);

    /**
     * checks whether directory contains any children
     *
     * @return  bool
     * @since   0.10.0
     */
    public function hasChildren();

    /**
     * returns a list of children for this directory
     *
     * @return  vfsStream_Interface_Content[]
     */
    public function getChildren();
}
?>
