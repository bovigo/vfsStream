<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\visitor;

use bovigo\vfs\BasicFile;
use bovigo\vfs\vfsBlock;
use bovigo\vfs\vfsDirectory;
use bovigo\vfs\vfsFile;

/**
 * Interface for a visitor to work on a vfsStream content structure.
 *
 * @see    https://github.com/mikey179/vfsStream/issues/10
 *
 * @since  0.10.0
 */
interface vfsStreamVisitor
{
    /**
     * visit a content and process it
     */
    public function visit(BasicFile $file): self;

    /**
     * visit a file and process it
     */
    public function visitFile(vfsFile $file): self;

    /**
     * visit a directory and process it
     */
    public function visitDirectory(vfsDirectory $dir): self;

    /**
     * visit a block device and process it
     */
    public function visitBlockDevice(vfsBlock $block): self;
}
