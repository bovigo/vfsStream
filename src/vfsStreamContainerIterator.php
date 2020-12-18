<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
class vfsStreamContainerIterator implements Iterator
{
    /**
     * list of children from container to iterate over
     *
     * @var  vfsStreamContent[]
     */
    protected $children;

    /**
     * constructor
     *
     * @param  vfsStreamContent[] $children
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
    public function rewind(): void
    {
        reset($this->children);
    }

    /**
     * returns the current child
     */
    public function current(): ?vfsStreamContent
    {
        $child = current($this->children);
        if ($child === false) {
            return null;
        }

        return $child;
    }

    /**
     * returns the name of the current child
     */
    public function key(): ?string
    {
        $child = current($this->children);
        if ($child === false) {
            return null;
        }

        return $child->getName();
    }

    /**
     * iterates to next child
     */
    public function next(): void
    {
        next($this->children);
    }

    /**
     * checks if the current value is valid
     */
    public function valid(): bool
    {
        return current($this->children) !== false;
    }
}

class_alias('bovigo\vfs\vfsStreamContainerIterator', 'org\bovigo\vfs\vfsStreamContainerIterator');
