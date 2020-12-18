<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
 * @see    https://github.com/mikey179/vfsStream/issues/10
 *
 * @since  0.10.0
 */
interface vfsStreamVisitor
{
    /**
     * visit a content and process it
     */
    public function visit(vfsStreamContent $content): self;

    /**
     * visit a file and process it
     */
    public function visitFile(vfsStreamFile $file): self;

    /**
     * visit a directory and process it
     */
    public function visitDirectory(vfsStreamDirectory $dir): self;

    /**
     * visit a block device and process it
     */
    public function visitBlockDevice(vfsStreamBlock $block): self;
}

class_alias('bovigo\vfs\visitor\vfsStreamVisitor', 'org\bovigo\vfs\visitor\vfsStreamVisitor');
