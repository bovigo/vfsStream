<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs\content;

use function class_alias;

/**
 * Interface for actual file contents.
 *
 * @since  1.3.0
 */
interface FileContent
{
    /**
     * returns actual content
     *
     * @return  string
     */
    public function content();

    /**
     * returns size of content
     *
     * @return  int
     */
    public function size();

    /**
     * reads the given amount of bytes from content
     *
     * @param   int     $count
     * @return  string
     */
    public function read($count);

    /**
     * seeks to the given offset
     *
     * @param   int   $offset
     * @param   int   $whence
     * @return  bool
     */
    public function seek($offset, $whence);

    /**
     * checks whether pointer is at end of file
     *
     * @return  bool
     */
    public function eof();

    /**
     * writes an amount of data
     *
     * @param   string  $data
     * @return  int amount of written bytes
     */
    public function write($data);

    /**
     * Truncates a file to a given length
     *
     * @param   int  $size length to truncate file to
     * @return  bool
     */
    public function truncate($size);
}

class_alias('bovigo\vfs\content\FileContent', 'org\bovigo\vfs\content\FileContent');
