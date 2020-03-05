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
use bovigo\vfs\vfsStreamContent;
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
     * @param   vfsFile  $file
     * @return  vfsStreamVisitor
     */
    public function visitFile(vfsFile $file);

    /**
     * visit a directory and process it
     *
     * @param   vfsDirectory  $dir
     * @return  vfsStreamVisitor
     */
    public function visitDirectory(vfsDirectory $dir);

    /**
     * visit a block device and process it
     *
     * @param   vfsBlock  $block
     * @return  vfsStreamVisitor
     */
    public function visitBlockDevice(vfsBlock $block);
}

class_alias('bovigo\vfs\visitor\vfsStreamVisitor', 'org\bovigo\vfs\visitor\vfsStreamVisitor');
