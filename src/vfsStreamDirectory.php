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

use function array_values;
use function class_alias;
use function count;
use function strlen;
use function substr;
use function time;

/**
 * Directory container.
 *
 * @api
 */
class vfsStreamDirectory extends vfsStreamAbstractContent implements vfsStreamContainer
{
    /**
     * list of directory children
     *
     * @var  vfsStreamContent[]
     */
    protected $children = [];

    /**
     * constructor
     *
     * @param   int|null $permissions optional
     *
     * @throws  vfsStreamException
     */
    public function __construct(string $name, ?int $permissions = null)
    {
        $this->type = vfsStreamContent::TYPE_DIR;
        parent::__construct($name, $permissions);
    }

    /**
     * returns default permissions for concrete implementation
     *
     * @since   0.8.0
     */
    protected function getDefaultPermissions(): int
    {
        return 0777;
    }

    /**
     * returns size of directory
     *
     * The size of a directory is always 0 bytes. To calculate the summarized
     * size of all children in the directory use sizeSummarized().
     */
    public function size(): int
    {
        return 0;
    }

    /**
     * returns summarized size of directory and its children
     */
    public function sizeSummarized(): int
    {
        $size = 0;
        foreach ($this->children as $child) {
            if ($child instanceof self) {
                $size += $child->sizeSummarized();
            } else {
                $size += $child->size();
            }
        }

        return $size;
    }

    /**
     * sets parent path
     *
     * @internal  only to be set by parent
     *
     * @since   1.2.0
     */
    public function setParentPath(string $parentPath): void
    {
        parent::setParentPath($parentPath);
        foreach ($this->children as $child) {
            $child->setParentPath($this->path());
        }
    }

    /**
     * adds child to the directory
     */
    public function addChild(vfsStreamContent $child): void
    {
        $child->setParentPath($this->path());
        $this->children[$child->getName()] = $child;
        $this->updateModifications();
    }

    /**
     * removes child from the directory
     */
    public function removeChild(string $name): bool
    {
        foreach ($this->children as $key => $child) {
            if ($child->appliesTo($name)) {
                $child->removeParentPath();
                unset($this->children[$key]);
                $this->updateModifications();

                return true;
            }
        }

        return false;
    }

    /**
     * updates internal timestamps
     */
    protected function updateModifications(): void
    {
        $time = time();
        $this->lastAttributeModified = $time;
        $this->lastModified = $time;
    }

    /**
     * checks whether the container contains a child with the given name
     */
    public function hasChild(string $name): bool
    {
        return $this->getChild($name) !== null;
    }

    /**
     * returns the child with the given name
     */
    public function getChild(string $name): ?vfsStreamContent
    {
        $childName = $this->getRealChildName($name);
        foreach ($this->children as $child) {
            if ($child->getName() === $childName) {
                return $child;
            }

            if (! $child instanceof vfsStreamContainer) {
                continue;
            }

            if ($child->appliesTo($childName) && $child->hasChild($childName)) {
                return $child->getChild($childName);
            }
        }

        return null;
    }

    /**
     * helper method to detect the real child name
     */
    protected function getRealChildName(string $name): string
    {
        if ($this->appliesTo($name) === true) {
            return self::getChildName($name, $this->name);
        }

        return $name;
    }

    /**
     * helper method to calculate the child name
     */
    protected static function getChildName(string $name, string $ownName): string
    {
        if ($name === $ownName) {
            return $name;
        }

        return substr($name, strlen($ownName) + 1);
    }

    /**
     * checks whether directory contains any children
     *
     * @since   0.10.0
     */
    public function hasChildren(): bool
    {
        return count($this->children) > 0;
    }

    /**
     * returns a list of children for this directory
     *
     * @return  vfsStreamContent[]
     */
    public function getChildren(): array
    {
        return array_values($this->children);
    }

    /**
     * returns iterator for the children
     *
     * @return  vfsStreamContainerIterator
     */
    public function getIterator(): Iterator
    {
        return new vfsStreamContainerIterator($this->children);
    }

    /**
     * checks whether dir is a dot dir
     */
    public function isDot(): bool
    {
        return $this->name === '.' || $this->name === '..';
    }
}

class_alias('bovigo\vfs\vfsStreamDirectory', 'org\bovigo\vfs\vfsStreamDirectory');
