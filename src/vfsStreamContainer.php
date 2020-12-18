<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs;

use IteratorAggregate;

use function class_alias;

/**
 * Interface for stream contents that are able to store other stream contents.
 */
interface vfsStreamContainer extends IteratorAggregate
{
    /**
     * adds child to the directory
     */
    public function addChild(vfsStreamContent $child): void;

    /**
     * removes child from the directory
     */
    public function removeChild(string $name): bool;

    /**
     * checks whether the container contains a child with the given name
     */
    public function hasChild(string $name): bool;

    /**
     * returns the child with the given name
     */
    public function getChild(string $name): ?vfsStreamContent;

    /**
     * checks whether directory contains any children
     *
     * @since   0.10.0
     */
    public function hasChildren(): bool;

    /**
     * returns a list of children for this directory
     *
     * @return  vfsStreamContent[]
     */
    public function getChildren(): array;
}

class_alias('bovigo\vfs\vfsStreamContainer', 'org\bovigo\vfs\vfsStreamContainer');
