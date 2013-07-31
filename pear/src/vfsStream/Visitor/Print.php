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
 * Visitor which traverses a content structure recursively to print it to an output stream.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 */
class vfsStream_Visitor_Print extends vfsStream_Abstract_Visitor
{
    /**
     * target to write output to
     *
     * @type  resource
     */
    protected $out;
    /**
     * current depth in directory tree
     *
     * @type  int
     */
    protected $depth;

    /**
     * constructor
     *
     * If no file pointer given it will fall back to STDOUT.
     *
     * @param   resource  $out  optional
     * @throws  \InvalidArgumentException
     * @api
     */
    public function __construct($out = STDOUT)
    {
        if (is_resource($out) === false || get_resource_type($out) !== 'stream') {
            throw new \InvalidArgumentException('Given filepointer is not a resource of type stream');
        }

        $this->out = $out;
    }

    /**
     * visit a file and process it
     *
     * @param   vfsStream_File  $file
     * @return  vfsStream_Visitor_Print
     */
    public function visitFile(vfsStream_File $file)
    {
        $this->printContent($file);
        return $this;
    }

    /**
     * visit a directory and process it
     *
     * @param   vfsStream_Directory  $dir
     * @return  vfsStream_Visitor_Print
     */
    public function visitDirectory(vfsStream_Directory $dir)
    {
        $this->printContent($dir);
        $this->depth++;
        foreach ($dir as $child) {
            $this->visit($child);
        }

        $this->depth--;
        return $this;
    }

    /**
     * helper method to print the content
     *
     * @param  vfsStream_Interface_Content  $content
     */
    protected function printContent(vfsStream_Interface_Content $content)
    {
        fwrite($this->out, str_repeat('  ', $this->depth) . '- ' . $content->getName() . "\n");
    }
}
?>
