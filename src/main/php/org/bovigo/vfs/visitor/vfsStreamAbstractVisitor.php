<?php
/**
 * Abstract base class providing an implementation for the visit() method.
 *
 * @package     bovigo_vfs
 * @subpackage  visitor
 */
/**
 * @ignore
 */
require_once dirname(__FILE__) . '/vfsStreamVisitor.php';
/**
 * Abstract base class providing an implementation for the visit() method.
 *
 * @package     bovigo_vfs
 * @subpackage  visitor
 * @since       0.10.0
 * @see         https://github.com/mikey179/vfsStream/issues/10
 */
abstract class vfsStreamAbstractVisitor implements vfsStreamVisitor
{
    /**
     * visit a content and process it
     *
     * @param   vfsStreamContent  $content
     * @return  vfsStreamVisitor
     * @throws  InvalidArgumentException
     */
    public function visit(vfsStreamContent $content)
    {
        switch ($content->getType()) {
            case vfsStreamContent::TYPE_FILE:
                $this->visitFile($content);
                break;

            case vfsStreamContent::TYPE_DIR:
                $this->visitDirectory($content);
                break;

            default:
                throw new InvalidArgumentException('Unknown content type');
        }

        return $this;
    }
}
?>