<?php
/**
 * Test for org::bovigo::vfs::vfsStreamWrapper.
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo_vfs
 * @subpackage  test
 */
require_once 'org/bovigo/vfs/vfsStream.php';
require_once 'PHPUnit/Framework.php';
/**
 * Test for org::bovigo::vfs::vfsStreamWrapper.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
class vfsStreamWrapperWithoutRootTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * set up test environment
     */
    public function setUp()
    {
        vfsStreamWrapper::register();
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
     * no root > can not create subdirectory
     *
     * @test
     */
    public function canNotCreateNewDirectoryWithmkdir()
    {
        $this->assertFalse(@mkdir(vfsStream::url('foo')));
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
     * can not unlink without root
     *
     * @test
     */
    public function canNotUnlinkDirectory()
    {
        $this->assertFalse(@rmdir(vfsStream::url('foo')));
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
        $this->assertFalse(rename(vfsStream::url('foo'), vfsStream::url('bar')));
    }
}
?>