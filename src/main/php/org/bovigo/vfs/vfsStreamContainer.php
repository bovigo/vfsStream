<?php
/**
 * Interface for stream contents that are able to store other stream contents.
 *
 * @package  bovigo_vfs
 */
/**
 * Interface for stream contents that are able to store other stream contents.
 *
 * @package  bovigo_vfs
 */
interface vfsStreamContainer extends IteratorAggregate
{
    /**
     * adds child to the directory
     *
     * @param  vfsStreamContent  $child
     */
    public function addChild(vfsStreamContent $child);

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
     * @return  vfsStreamContent
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
     * @return  array<vfsStreamContent>
     */
    public function getChildren();
}
?>