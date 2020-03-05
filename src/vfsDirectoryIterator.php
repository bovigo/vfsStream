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

use Iterator;
use function array_unshift;
use function class_alias;
use function current;
use function next;
use function reset;

/**
 * Iterator for children of a directory container.
 */
class vfsDirectoryIterator implements \Iterator
{
    /**
     * list of children from container to iterate over
     *
     * @type  vfsStreamContent[]
     */
    protected $children;

    /**
     * constructor
     *
     * @param  vfsStreamContent[]  $children
     */
    public function __construct(array $children)
    {
        $this->children = $children;
        if (vfsStream::useDotfiles()) {
            array_unshift($this->children, new DotDirectory('.'), new DotDirectory('..'));
        }

        reset($this->children);
    }

    /**
     * resets children pointer
     */
    public function rewind()
    {
        reset($this->children);
    }

    /**
     * returns the current child
     *
     * @return  vfsStreamContent
     */
    public function current()
    {
        $child = current($this->children);
        if (false === $child) {
            return null;
        }

        return $child;
    }

    /**
     * returns the name of the current child
     *
     * @return  string
     */
    public function key()
    {
        $child = current($this->children);
        if (false === $child) {
            return null;
        }

        return $child->getName();
    }

    /**
     * iterates to next child
     */
    public function next()
    {
        next($this->children);
    }

    /**
     * checks if the current value is valid
     *
     * @return  bool
     */
    public function valid()
    {
        return (false !== current($this->children));
    }
}

class_alias('bovigo\vfs\vfsDirectoryIterator', 'org\bovigo\vfs\vfsStreamContainerIterator');
