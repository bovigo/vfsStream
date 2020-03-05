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

use bovigo\vfs\vfsStreamBlock;
use bovigo\vfs\vfsStreamContent;
use bovigo\vfs\vfsStreamDirectory;
use bovigo\vfs\vfsStreamFile;
use function class_alias;

/**
 * Interface for a visitor to work on a vfsStream content structure.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 */
interface vfsStreamVisitor
{
    /**
     * visit a content and process it
     *
     * @param   vfsStreamContent  $content
     * @return  vfsStreamVisitor
     */
    public function visit(vfsStreamContent $content);

    /**
     * visit a file and process it
     *
     * @param   vfsStreamFile  $file
     * @return  vfsStreamVisitor
     */
    public function visitFile(vfsStreamFile $file);

    /**
     * visit a directory and process it
     *
     * @param   vfsStreamDirectory  $dir
     * @return  vfsStreamVisitor
     */
    public function visitDirectory(vfsStreamDirectory $dir);

    /**
     * visit a block device and process it
     *
     * @param   vfsStreamBlock  $block
     * @return  vfsStreamVisitor
     */
    public function visitBlockDevice(vfsStreamBlock $block);
}

class_alias('bovigo\vfs\visitor\vfsStreamVisitor', 'org\bovigo\vfs\visitor\vfsStreamVisitor');
