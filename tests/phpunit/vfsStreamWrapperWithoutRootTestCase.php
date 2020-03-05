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
use bovigo\vfs\StreamWrapper;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertFalse;
use function dir;
use function fopen;
use function rename;
use function unlink;

/**
 * Test for bovigo\vfs\StreamWrapper.
 */
class vfsStreamWrapperWithoutRootTestCase extends \BC_PHPUnit_Framework_TestCase
{
    /**
     * set up test environment
     */
    public function setUp()
    {
        StreamWrapper::register();
    }

    /**
     * no root > no directory to open
     *
     * @test
     */
    public function canNotOpenDirectory()
    {
        $this->assertFalse(@dir(vfsStream::url('foo')));
    }

    /**
     * can not unlink without root
     *
     * @test
     */
    public function canNotUnlink()
    {
        $this->assertFalse(@unlink(vfsStream::url('foo')));
    }

    /**
     * can not open a file without root
     *
     * @test
     */
    public function canNotOpen()
    {
        $this->assertFalse(@fopen(vfsStream::url('foo'), 'r'));
    }

    /**
     * can not rename a file without root
     *
     * @test
     */
    public function canNotRename()
    {
        $this->assertFalse(@rename(vfsStream::url('foo'), vfsStream::url('bar')));
    }
}
