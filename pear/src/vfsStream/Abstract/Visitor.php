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
 * Abstract base class providing an implementation for the visit() method.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 */
abstract class vfsStream_Abstract_Visitor implements vfsStream_Interface_Visitor
{
    /**
     * visit a content and process it
     *
     * @param   vfsStream_Interface_Content  $content
     * @return  vfsStream_Interface_Visitor
     * @throws  InvalidArgumentException
     */
    public function visit(vfsStream_Interface_Content $content)
    {
        switch ($content->getType()) {
            case vfsStream_Interface_Content::TYPE_FILE:
                $this->visitFile($content);
                break;

            case vfsStream_Interface_Content::TYPE_DIR:
                if (!$content->isDot()) {
                    $this->visitDirectory($content);
                }

                break;

            default:
                throw new \InvalidArgumentException('Unknown content type ' . $content->getType() . ' for ' . $content->getName());
        }

        return $this;
    }
}
?>
