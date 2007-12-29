<?php
/**
 * Test for org::stubbles::vfs::vfsStream.
 *
 * @author      Frank Kleine <mikey@stubbles.net>
 * @package     stubbles_vfs
 * @subpackage  test
 */
require_once SRC_PATH . '/main/php/org/stubbles/vfs/vfsStream.php';
Mock::generate('vfsStreamContent');
/**
 * Test for org::stubbles::vfs::vfsStream.
 *
 * @package     stubbles_vfs
 * @subpackage  test
 */
class vfsStreamTestCase extends UnitTestCase
{
    /**
     * assure that path2url conversion works correct
     */
    public function testURL()
    {
        $this->assertEqual('vfs://foo', vfsStream::url('foo'));
        $this->assertEqual('vfs://foo/bar.baz', vfsStream::url('foo/bar.baz'));
        $this->assertEqual('vfs://foo/bar.baz', vfsStream::url('foo\bar.baz'));
    }

    /**
     * assure that url2path conversion works correct
     */
    public function testPath()
    {
        $this->assertEqual('foo', vfsStream::path('vfs://foo'));
        $this->assertEqual('foo/bar.baz', vfsStream::path('vfs://foo/bar.baz'));
        $this->assertEqual('foo/bar.baz', vfsStream::path('vfs://foo\bar.baz'));
    }

    /**
     * test to create a new file
     */
    public function testNewFile()
    {
        $file = vfsStream::newFile('filename.txt');
        $this->assertIsA($file, 'vfsStreamFile');
        $this->assertEqual('filename.txt', $file->getName());
    }
}
?>