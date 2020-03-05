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

/**
 * Block container.
 *
 * @api
 */
class vfsBlock extends vfsFile
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
    }

    /**
     * returns the type of the file
     */
    public function type(): int
    {
        return Type::BLOCK;
    }
}
