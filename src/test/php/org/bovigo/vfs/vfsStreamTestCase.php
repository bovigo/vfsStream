<?php
/**
 * Test for org::bovigo::vfs::vfsStream.
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo_vfs
 * @subpackage  test
 */
require_once 'org/bovigo/vfs/vfsStream.php';
require_once 'PHPUnit/Framework.php';
/**
 * Test for org::bovigo::vfs::vfsStream.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
class vfsStreamTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * assure that path2url conversion works correct
     *
     * @test
     */
    public function url()
    {
        $this->assertEquals('vfs://foo', vfsStream::url('foo'));
        $this->assertEquals('vfs://foo/bar.baz', vfsStream::url('foo/bar.baz'));
        $this->assertEquals('vfs://foo/bar.baz', vfsStream::url('foo\bar.baz'));
    }

    /**
     * assure that url2path conversion works correct
     *
     * @test
     */
    public function path()
    {
        $this->assertEquals('foo', vfsStream::path('vfs://foo'));
        $this->assertEquals('foo/bar.baz', vfsStream::path('vfs://foo/bar.baz'));
        $this->assertEquals('foo/bar.baz', vfsStream::path('vfs://foo\bar.baz'));
    }

    /**
     * test to create a new file
     *
     * @test
     */
    public function newFile()
    {
        $file = vfsStream::newFile('filename.txt');
        $this->assertType('vfsStreamFile', $file);
        $this->assertEquals('filename.txt', $file->getName());
    }
}
?>