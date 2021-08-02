<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs;

use ArrayIterator;
use Iterator;
use function class_alias;

/**
 * Directory container.
 */
class DotDirectory extends vfsDirectory
{
    /**
     * returns iterator for the children
     *
     * @return  vfsDirectoryIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator(array());
    }

    /**
     * checks whether dir is a dot dir
     *
     * @return  bool
     */
    public function isDot()
    {
        return true;
    }
}

class_alias('bovigo\vfs\DotDirectory', 'org\bovigo\vfs\DotDirectory');
