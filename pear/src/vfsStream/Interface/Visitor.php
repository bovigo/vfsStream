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
 * Interface for a visitor to work on a vfsStream content structure.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 */
interface vfsStream_Interface_Visitor
{
    /**
     * visit a content and process it
     *
     * @param   vfsStream_Content  $content
     * @return  vfsStream_Visitor
     */
    public function visit(vfsStream_Interface_Content $content);

    /**
     * visit a file and process it
     *
     * @param   vfsStream_File  $file
     * @return  vfsStream_Visitor
     */
    public function visitFile(vfsStream_File $file);

    /**
     * visit a directory and process it
     *
     * @param   vfsStream_Directory  $dir
     * @return  vfsStream_Visitor
     */
    public function visitDirectory(vfsStream_Directory $dir);
}
?>
