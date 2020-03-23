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

use IteratorAggregate;
use function class_alias;

/**
 * Interface for stream contents that are able to store other stream contents.
 * 
 * @deprecated since 1.7, will be removed in version 2
 */
interface vfsStreamContainer extends \IteratorAggregate
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
     * @return  vfsStreamContent[]
     */
    public function getChildren();
}

class_alias('bovigo\vfs\vfsStreamContainer', 'org\bovigo\vfs\vfsStreamContainer');
