<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

/**
 * Directory container.
 */
class vfsStream_DotDirectory extends vfsStream_Directory
{
    /**
     * returns iterator for the children
     *
     * @return  vfsStream_ContainerIterator
     */
    public function getIterator()
    {
        return new ArrayIterator(array());
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
?>
