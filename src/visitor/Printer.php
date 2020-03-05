<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\visitor;

use bovigo\vfs\vfsBlock;
use bovigo\vfs\vfsDirectory;
use bovigo\vfs\vfsFile;
use InvalidArgumentException;
use const STDOUT;
use function fwrite;
use function get_resource_type;
use function is_resource;
use function str_repeat;

/**
 * Visitor which traverses a content structure recursively to print it to an output stream.
 *
 * @see    https://github.com/mikey179/vfsStream/issues/10
 *
 * @since  0.10.0
 */
class Printer extends BaseVisitor
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
    protected $depth = 0;

    /**
     * constructor
     *
     * If no file pointer given it will fall back to STDOUT.
     *
     * @param   resource $out optional
     *
     * @throws InvalidArgumentException
     *
     * @api
     */
    public function __construct($out = STDOUT)
    {
        if (! is_resource($out) || get_resource_type($out) !== 'stream') {
            throw new InvalidArgumentException('Given filepointer is not a resource of type stream');
        }

        $this->out = $out;
    }

    /**
     * visit a file and process it
     *
     * @return  Printer
     */
    public function visitFile(vfsFile $file): vfsStreamVisitor
    {
        $this->printContent($file->name());

        return $this;
    }

    /**
     * visit a block device and process it
     *
     * @return  Printer
     */
    public function visitBlockDevice(vfsBlock $block): vfsStreamVisitor
    {
        $name = '[' . $block->name() . ']';
        $this->printContent($name);

        return $this;
    }

    /**
     * visit a directory and process it
     *
     * @return  Printer
     */
    public function visitDirectory(vfsDirectory $dir): vfsStreamVisitor
    {
        $this->printContent($dir->name());
        $this->depth++;
        foreach ($dir as $child) {
            $this->visit($child);
        }

        $this->depth--;

        return $this;
    }

    /**
     * helper method to print the content
     */
    protected function printContent(string $name): void
    {
        fwrite($this->out, str_repeat('  ', $this->depth) . '- ' . $name . "\n");
    }
}
