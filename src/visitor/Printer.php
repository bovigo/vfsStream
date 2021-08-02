<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs\visitor;

use bovigo\vfs\vfsBlock;
use bovigo\vfs\vfsDirectory;
use bovigo\vfs\vfsFile;
use InvalidArgumentException;
use const STDOUT;
use function class_alias;
use function fwrite;
use function get_resource_type;
use function is_resource;
use function str_repeat;

/**
 * Visitor which traverses a content structure recursively to print it to an output stream.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 */
class Printer extends BaseVisitor
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
        $this->depth = 0;
    }

    /**
     * visit a file and process it
     *
     * @param   vfsFile  $file
     * @return  Printer
     */
    public function visitFile(vfsFile $file)
    {
        $this->printContent($file->getName());
        return $this;
    }

    /**
     * visit a block device and process it
     *
     * @param   vfsBlock  $block
     * @return  Printer
     */
    public function visitBlockDevice(vfsBlock $block)
    {
        $name = '[' . $block->getName() . ']';
        $this->printContent($name);
        return $this;
    }

    /**
     * visit a directory and process it
     *
     * @param   vfsDirectory  $dir
     * @return  Printer
     */
    public function visitDirectory(vfsDirectory $dir)
    {
        $this->printContent($dir->getName());
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
     * @param  string   $name
     */
    protected function printContent($name)
    {
        fwrite($this->out, str_repeat('  ', $this->depth) . '- ' . $name . "\n");
    }
}

class_alias('bovigo\vfs\visitor\Printer', 'org\bovigo\vfs\visitor\vfsStreamPrintVisitor');
