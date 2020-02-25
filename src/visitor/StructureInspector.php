<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\visitor;

use bovigo\vfs\vfsBlock;
use bovigo\vfs\vfsDirectory;
use bovigo\vfs\vfsFile;
use function class_alias;

/**
 * Visitor which traverses a content structure recursively to create an array structure from it.
 *
 * @see    https://github.com/mikey179/vfsStream/issues/10
 *
 * @since  0.10.0
 */
class StructureInspector extends AbstractVisitor
{
    /**
     * collected structure
     *
     * @var  string[]
     */
    protected $structure = [];
    /**
     * poiting to currently iterated directory
     *
     * @var  mixed[]
     */
    protected $current;

    /**
     * constructor
     *
     * @api
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * visit a file and process it
     *
     * @return  StructureInspector
     */
    public function visitFile(vfsFile $file): vfsStreamVisitor
    {
        $this->current[$file->name()] = $file->content();

        return $this;
    }

    /**
     * visit a block device and process it
     *
     * @return  StructureInspector
     */
    public function visitBlockDevice(vfsBlock $block): vfsStreamVisitor
    {
        $this->current['[' . $block->name() . ']'] = $block->content();

        return $this;
    }

    /**
     * visit a directory and process it
     *
     * @return  StructureInspector
     */
    public function visitDirectory(vfsDirectory $dir): vfsStreamVisitor
    {
        $this->current[$dir->name()] = [];
        $tmp                            =& $this->current;
        $this->current                  =& $tmp[$dir->name()];
        foreach ($dir as $child) {
            $this->visit($child);
        }

        $this->current =& $tmp;

        return $this;
    }

    /**
     * returns structure of visited contents
     *
     * @return string[]
     *
     * @api
     */
    public function getStructure(): array
    {
        return $this->structure;
    }

    /**
     * resets structure so visitor could be reused
     */
    public function reset(): self
    {
        $this->structure = [];
        $this->current   =& $this->structure;

        return $this;
    }
}

class_alias('bovigo\vfs\visitor\StructureInspector', 'org\bovigo\vfs\visitor\vfsStreamStructureVisitor');
