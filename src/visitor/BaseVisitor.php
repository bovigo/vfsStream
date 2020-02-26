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
use InvalidArgumentException;
use function class_alias;

/**
 * Abstract base class providing an implementation for the visit() method.
 *
 * @see    https://github.com/mikey179/vfsStream/issues/10
 *
 * @since  0.10.0
 */
abstract class BaseVisitor implements vfsStreamVisitor
{
    /**
     * visit a content and process it
     *
     * @throws InvalidArgumentException
     */
    public function visit(BasicFile $file): vfsStreamVisitor
    {
        if ($file instanceof vfsBlock) {
            $this->visitBlockDevice($file);
        } elseif ($file instanceof vfsFile) {
            $this->visitFile($file);
        } elseif ($file instanceof vfsDirectory) {
            if (! $file->isDot()) {
                $this->visitDirectory($file);
            }
        } else {
            throw new InvalidArgumentException(
                'Unknown content type ' . $file->type() . ' for ' . $file->name()
            );
        }

        return $this;
    }

    /**
     * visit a block device and process it
     */
    public function visitBlockDevice(vfsBlock $block): vfsStreamVisitor
    {
        return $this->visitFile($block);
    }
}

class_alias('bovigo\vfs\visitor\BaseVisitor', 'org\bovigo\vfs\visitor\vfsStreamAbstractVisitor');
