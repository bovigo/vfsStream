<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs\visitor;

use bovigo\vfs\vfsBlock;
use bovigo\vfs\vfsDirectory;
use bovigo\vfs\vfsFile;
use function class_alias;

/**
 * Visitor which traverses a content structure recursively to create an array structure from it.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 */
class StructureInspector extends BaseVisitor
{
    /**
     * collected structure
     *
     * @type  array
     */
    protected $structure = array();
    /**
     * poiting to currently iterated directory
     *
     * @type  array
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
     * @param   vfsFile  $file
     * @return  StructureInspector
     */
    public function visitFile(vfsFile $file)
    {
        $this->current[$file->getName()] = $file->getContent();
        return $this;
    }

    /**
     * visit a block device and process it
     *
     * @param   vfsBlock $block
     * @return  StructureInspector
     */
    public function visitBlockDevice(vfsBlock $block)
    {
        $this->current['[' . $block->getName() . ']'] = $block->getContent();
        return $this;
    }

    /**
     * visit a directory and process it
     *
     * @param   vfsDirectory  $dir
     * @return  StructureInspector
     */
    public function visitDirectory(vfsDirectory $dir)
    {
        $this->current[$dir->getName()] = array();
        $tmp           =& $this->current;
        $this->current =& $tmp[$dir->getName()];
        foreach ($dir as $child) {
            $this->visit($child);
        }

        $this->current =& $tmp;
        return $this;
    }

    /**
     * returns structure of visited contents
     *
     * @return  array
     * @api
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * resets structure so visitor could be reused
     *
     * @return  StructureInspector
     */
    public function reset()
    {
        $this->structure = array();
        $this->current   =& $this->structure;
        return $this;
    }
}

class_alias('bovigo\vfs\visitor\StructureInspector', 'org\bovigo\vfs\visitor\vfsStreamStructureVisitor');
