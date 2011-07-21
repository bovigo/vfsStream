<?php
/**
 * Visitor which traverses a content structure recursively to print it to an output stream.
 *
 * @package     bovigo_vfs
 * @subpackage  visitor
 */
/**
 * @ignore
 */
require_once dirname(__FILE__) . '/vfsStreamAbstractVisitor.php';
/**
 * Visitor which traverses a content structure recursively to print it to an output stream.
 *
 * @package     bovigo_vfs
 * @subpackage  visitor
 * @since       0.10.0
 * @see         https://github.com/mikey179/vfsStream/issues/10
 */
class vfsStreamPrintVisitor extends vfsStreamAbstractVisitor
{
    /**
     * target to write output to
     *
     * @var  resource
     */
    protected $out;
    /**
     * current depth in directory tree
     *
     * @var  int
     */
    protected $depth;

    /**
     * constructor
     *
     * If no file pointer given it will fall back to STDOUT.
     *
     * @param   resource  $out  optional
     * @throws  InvalidArgumentException
     */
    public function __construct($out = STDOUT)
    {
        if (is_resource($out) === false || get_resource_type($out) !== 'stream') {
            throw new InvalidArgumentException('Given filepointer is not a resource of type stream');
        }

        $this->out = $out;
    }

    /**
     * visit a file and process it
     *
     * @param   vfsStreamFile          $file
     * @return  vfsStreamPrintVisitor
     */
    public function visitFile(vfsStreamFile $file)
    {
        $this->printContent($file);
        return $this;
    }

    /**
     * visit a directory and process it
     *
     * @param   vfsStreamDirectory     $dir
     * @return  vfsStreamPrintVisitor
     */
    public function visitDirectory(vfsStreamDirectory $dir)
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
     * @param  vfsStreamContent  $content
     */
    protected function printContent(vfsStreamContent $content)
    {
        fwrite($this->out, str_repeat('  ', $this->depth) . '- ' . $content->getName() . "\n");
    }
}
?>