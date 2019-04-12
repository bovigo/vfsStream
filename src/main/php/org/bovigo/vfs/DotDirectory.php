<?php
declare(strict_types=1);
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
namespace org\bovigo\vfs;
/**
 * Directory container.
 */
class DotDirectory extends vfsStreamDirectory
{
    /**
     * returns iterator for the children
     *
     * @return  \Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator([]);
    }

    /**
     * checks whether dir is a dot dir
     *
     * @return  bool
     */
    public function isDot(): bool
    {
        return true;
    }
}
