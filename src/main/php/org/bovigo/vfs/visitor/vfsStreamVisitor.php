<?php
/**
 * Interface for a visitor to work on a vfsStream content structure.
 *
 * @package     bovigo_vfs
 * @subpackage  visitor
 */
/**
 * @ignore
 */
require_once dirname(__FILE__) . '/../vfsStreamDirectory.php';
require_once dirname(__FILE__) . '/../vfsStreamFile.php';
/**
 * Interface for a visitor to work on a vfsStream content structure.
 *
 * @package     bovigo_vfs
 * @subpackage  visitor
 * @since       0.10.0
 * @see         https://github.com/mikey179/vfsStream/issues/10
 */
interface vfsStreamVisitor
{
    /**
     * visit a content and process it
     *
     * @param   vfsStreamContent  $content
     * @return  vfsStreamVisitor
     */
    public function visit(vfsStreamContent $content);

    /**
     * visit a file and process it
     *
     * @param   vfsStreamFile  $file
     * @return  vfsStreamVisitor
     */
    public function visitFile(vfsStreamFile $file);

    /**
     * visit a directory and process it
     *
     * @param   vfsStreamDirectory  $dir
     * @return  vfsStreamVisitor
     */
    public function visitDirectory(vfsStreamDirectory $dir);
}
?>