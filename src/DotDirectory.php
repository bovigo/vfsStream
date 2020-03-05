<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs;

use ArrayIterator;
use Iterator;

/**
 * Directory container.
 */
class DotDirectory extends vfsDirectory
{
    /**
     * returns iterator for the children
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator([]);
    }

    /**
     * checks whether dir is a dot dir
     */
    public function isDot(): bool
    {
        return true;
    }
}
