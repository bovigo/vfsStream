<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  orgbovigovfs
 */
require_once __DIR__ . '/../bootstrap/default.php';
/**
 * Test for vfsStream_Wrapper.
 */
class vfsStreamWrapperWithoutRootTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * set up test environment
     */
    public function setUp()
    {
        vfsStream_Wrapper::register();
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
        $this->assertFalse(@fopen(vfsStream::url('foo')));
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
?>
