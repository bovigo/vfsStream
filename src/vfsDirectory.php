<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs;

use bovigo\vfs\internal\Type;
use Iterator;
use IteratorAggregate;
use function array_values;
use function count;
use function strlen;
use function strncmp;
use function substr;

/**
 * Directory container.
 *
 * @api
 */
class vfsDirectory extends BasicFile implements IteratorAggregate
{
    /**
     * list of directory children
     *
     * @var  BasicFile[]
     */
    private $children = [];
    /** @var  bool */
    private $isDot;
    /**
     * default directory permissions
     */
    public const DEFAULT_PERMISSIONS = 0777;

    /**
     * constructor
     *
     * @param   int|null $permissions optional
     */
    public function __construct(string $name, ?int $permissions = null)
    {
        parent::__construct($name, $permissions ?? (self::DEFAULT_PERMISSIONS & ~vfsStream::umask()));
        $this->isDot = $name === '.' || $name === '..';
    }

    /**
     * returns the type of the file
     */
    public function type(): int
    {
        return Type::DIR;
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
     * checks whether the directory can be applied to given name
     */
    public function appliesTo(string $name): bool
    {
        if (parent::appliesTo($name)) {
            return true;
        }

        $segment_name = $this->name() . '/';

        return strncmp($segment_name, $name, strlen($segment_name)) === 0;
    }

    /**
     * adds directory to given directory
     */
    public function at(self $directory): self
    {
        $directory->addChild($this);

        return $this;
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
    public function addChild(BasicFile $child): void
    {
        $child->setParentPath($this->path());
        $this->children[$child->name()] = $child;
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
     * checks whether the container contains a child with the given name
     */
    public function hasChild(string $name): bool
    {
        return $this->getChild($name) !== null;
    }

    /**
     * returns the child with the given name
     */
    public function getChild(string $name): ?BasicFile
    {
        $childName = $this->getRealChildName($name);
        foreach ($this->children as $child) {
            if ($child->name() === $childName) {
                return $child;
            }

            if (! $child instanceof self) {
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
            return self::getChildName($name, $this->name());
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
     * @return  BasicFile[]
     */
    public function getChildren(): array
    {
        return array_values($this->children);
    }

    /**
     * returns iterator for the children
     *
     * @return  vfsDirectoryIterator
     */
    public function getIterator(): Iterator
    {
        return new vfsDirectoryIterator($this->children);
    }

    /**
     * checks whether dir is a dot dir
     */
    public function isDot(): bool
    {
        return $this->isDot;
    }
}
