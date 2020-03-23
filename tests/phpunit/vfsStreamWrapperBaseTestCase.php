<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsDirectory;
use bovigo\vfs\vfsFile;
use bovigo\vfs\StreamWrapper;
use PHPUnit\Framework\TestCase;

/**
 * Test for bovigo\vfs\StreamWrapper.
 */
abstract class vfsStreamWrapperBaseTestCase extends \BC_PHPUnit_Framework_TestCase
{
    /**
     * root directory
     *
     * @var  vfsDirectory
     */
    protected $foo;
    /**
     * URL of root directory
     *
     * @var  string
     */
    protected $fooURL;
    /**
     * sub directory
     *
     * @var  vfsDirectory
     */
    protected $bar;
    /**
     * URL of sub directory
     *
     * @var  string
     */
    protected $barURL;
    /**
     * a file
     *
     * @var  vfsFile
     */
    protected $baz1;
    /**
     * URL of file 1
     *
     * @var  string
     */
    protected $baz1URL;
    /**
     * another file
     *
     * @var  vfsFile
     */
    protected $baz2;
    /**
     * URL of file 2
     *
     * @var  string
     */
    protected $baz2URL;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->fooURL  = vfsStream::url('foo');
        $this->barURL  = vfsStream::url('foo/bar');
        $this->baz1URL = vfsStream::url('foo/bar/baz1');
        $this->baz2URL = vfsStream::url('foo/baz2');
        $this->foo     = new vfsDirectory('foo');
        $this->bar     = new vfsDirectory('bar');
        $this->baz1    = vfsStream::newFile('baz1')
                                  ->lastModified(300)
                                  ->lastAccessed(300)
                                  ->lastAttributeModified(300)
                                  ->withContent('baz 1');
        $this->baz2    = vfsStream::newFile('baz2')
                                  ->withContent('baz2')
                                  ->lastModified(400)
                                  ->lastAccessed(400)
                                  ->lastAttributeModified(400);
        $this->bar->addChild($this->baz1);
        $this->foo->addChild($this->bar);
        $this->foo->addChild($this->baz2);
        $this->foo->lastModified(100)
                  ->lastAccessed(100)
                  ->lastAttributeModified(100);
        $this->bar->lastModified(200)
                  ->lastAccessed(100)
                  ->lastAttributeModified(100);
        StreamWrapper::register();
        StreamWrapper::setRoot($this->foo);
    }
}
