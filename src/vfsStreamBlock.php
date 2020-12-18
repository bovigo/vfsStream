<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs;

use function class_alias;

/**
 * Block container.
 *
 * @api
 */
class vfsStreamBlock extends vfsStreamFile
{
    /**
     * constructor
     *
     * @param  int|null $permissions optional
     */
    public function __construct(string $name, ?int $permissions = null)
    {
        if (empty($name)) {
            throw new vfsStreamException('Name of Block device was empty');
        }

        parent::__construct($name, $permissions);

        $this->type = vfsStreamContent::TYPE_BLOCK;
    }
}

class_alias('bovigo\vfs\vfsStreamBlock', 'org\bovigo\vfs\vfsStreamBlock');
