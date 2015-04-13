<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
namespace org\bovigo\vfs\visitor;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStreamBlock;

/**
 * Visitor which traverses a content structure recursively to return it as a string.
 *
 * @see    https://github.com/mikey179/vfsStream/issues/10
 */
class vfsStreamAssertVisitor extends vfsStreamAbstractVisitor {
	/**
	 * @type  string
	 */
	protected $out;
	/**
	 * current depth in directory tree
	 *
	 * @type  int
	 */
	protected $depth;

	/**
	 * visit a file and process it
	 *
	 * @param   vfsStreamFile $file
	 * @return  vfsStreamPrintVisitor
	 */
	public function visitFile(vfsStreamFile $file) {
		$this->printContent($file->getName(), $file->getPermissions(), '-');
		return $this;
	}

	/**
	 * visit a block device and process it
	 *
	 * @param   vfsStreamBlock $block
	 * @return  vfsStreamPrintVisitor
	 */
	public function visitBlockDevice(vfsStreamBlock $block) {
		$name = '[' . $block->getName() . ']';
		$this->printContent($name, $block->getPermissions(), ' ');
		return $this;
	}

	/**
	 * visit a directory and process it
	 *
	 * @param   vfsStreamDirectory $dir
	 * @return  vfsStreamPrintVisitor
	 */
	public function visitDirectory(vfsStreamDirectory $dir) {
		$this->printContent($dir->getName(), $dir->getPermissions(), '=');
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
	 * @param  string $name
	 * @param $permissions
	 * @param $fileOrFolderMark
	 * @return string
	 */
	protected function printContent($name, $permissions, $fileOrFolderMark) {
		$this->out .= $this->depth>0 ? "\n" : "";
		return $this->out .= str_repeat('.', $this->depth) . '\\' . $fileOrFolderMark . $name . ' @' . decoct($permissions);
	}

	public function getStructure() {
		return $this->out;
	}
}
