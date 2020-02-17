<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamDirectory;
use bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;

/**
 * Test for bovigo\vfs\vfsStreamWrapper.
 */
abstract class vfsStreamWrapperBaseTestCase extends TestCase
{
    /**
     * root directory
     *
     * @var  vfsStreamDirectory
     */
    protected $root;
    /**
     * sub directory
     *
     * @var  vfsStreamDirectory
     */
    protected $subdir;
    /**
     * a file
     *
     * @var  vfsStreamFile
     */
    protected $fileInSubdir;
    /**
     * another file
     *
     * @var  vfsStreamFile
     */
    protected $fileInRoot;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
        $this->subdir = vfsStream::newDirectory('subdir')->at($this->root);
        $this->fileInSubdir = vfsStream::newFile('file1')
            ->withContent('file 1')
            ->at($this->subdir);
        $this->fileInRoot = vfsStream::newFile('file2')
            ->withContent('file 2')
            ->at($this->root);
    }
}
