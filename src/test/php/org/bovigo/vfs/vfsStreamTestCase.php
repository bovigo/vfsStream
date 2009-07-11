<?php
/**
 * Test for org::bovigo::vfs::vfsStream.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 * @version     $Id$
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
     * windows directory separators are converted into default separator
     *
     * @author  Gabriel Birke
     * @test
     */
    public function pathConvertsWindowsDirectorySeparators()
    {
        $this->assertEquals('foo/bar', vfsStream::path('vfs://foo\\bar'));
    }

    /**
     * trailing whitespace should be removed
     *
     * @author  Gabriel Birke
     * @test
     */
    public function pathRemovesTrailingWhitespace()
    {
        $this->assertEquals('foo/bar', vfsStream::path('vfs://foo/bar '));
    }

    /**
     * trailing slashes are removed
     *
     * @author  Gabriel Birke
     * @test
     */
    public function pathRemovesTrailingSlash()
    {
        $this->assertEquals('foo/bar', vfsStream::path('vfs://foo/bar/'));
    }

    /**
     * trailing slash and whitespace should be removed
     *
     * @author  Gabriel Birke
     * @test
     */
    public function pathRemovesTrailingSlashAndWhitespace()
    {
        $this->assertEquals('foo/bar', vfsStream::path('vfs://foo/bar/ '));
    }

    /**
     * double slashes should be replaced by single slash
     *
     * @author  Gabriel Birke
     * @test
     */
    public function pathRemovesDoubleSlashes()
    {
        // Regular path
        $this->assertEquals('my/path', vfsStream::path('vfs://my/path'));
        // Path with double slashes
        $this->assertEquals('my/path', vfsStream::path('vfs://my//path'));
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
        $this->assertEquals(0777, $file->getPermissions());
    }

    /**
     * test to create a new file with non-default permissions
     *
     * @test
     * @group  permissions
     */
    public function newFileWithDifferentPermissions()
    {
        $file = vfsStream::newFile('filename.txt', 0644);
        $this->assertType('vfsStreamFile', $file);
        $this->assertEquals('filename.txt', $file->getName());
        $this->assertEquals(0644, $file->getPermissions());
    }

    /**
     * test to create a new directory structure
     *
     * @test
     */
    public function newSingleDirectory()
    {
        $foo = vfsStream::newDirectory('foo');
        $this->assertEquals('foo', $foo->getName());
        $this->assertEquals(0, count($foo->getChildren()));
        $this->assertEquals(0777, $foo->getPermissions());
    }

    /**
     * test to create a new directory structure with non-default permissions
     *
     * @test
     * @group  permissions
     */
    public function newSingleDirectoryWithDifferentPermissions()
    {
        $foo = vfsStream::newDirectory('foo', 0755);
        $this->assertEquals('foo', $foo->getName());
        $this->assertEquals(0, count($foo->getChildren()));
        $this->assertEquals(0755, $foo->getPermissions());
    }

    /**
     * test to create a new directory structure
     *
     * @test
     */
    public function newDirectoryStructure()
    {
        $foo = vfsStream::newDirectory('foo/bar/baz');
        $this->assertEquals('foo', $foo->getName());
        $this->assertEquals(0777, $foo->getPermissions());
        $this->assertTrue($foo->hasChild('bar'));
        $this->assertTrue($foo->hasChild('bar/baz'));
        $this->assertFalse($foo->hasChild('baz'));
        $bar = $foo->getChild('bar');
        $this->assertEquals('bar', $bar->getName());
        $this->assertEquals(0777, $bar->getPermissions());
        $this->assertTrue($bar->hasChild('baz'));
        $baz1 = $bar->getChild('baz');
        $this->assertEquals('baz', $baz1->getName());
        $this->assertEquals(0777, $baz1->getPermissions());
        $baz2 = $foo->getChild('bar/baz');
        $this->assertSame($baz1, $baz2);
    }

    /**
     * test that correct directory structure is created
     *
     * @test
     */
    public function newDirectoryWithSlashAtStart()
    {
        $foo = vfsStream::newDirectory('/foo/bar/baz', 0755);
        $this->assertEquals('foo', $foo->getName());
        $this->assertEquals(0755, $foo->getPermissions());
        $this->assertTrue($foo->hasChild('bar'));
        $this->assertTrue($foo->hasChild('bar/baz'));
        $this->assertFalse($foo->hasChild('baz'));
        $bar = $foo->getChild('bar');
        $this->assertEquals('bar', $bar->getName());
        $this->assertEquals(0755, $bar->getPermissions());
        $this->assertTrue($bar->hasChild('baz'));
        $baz1 = $bar->getChild('baz');
        $this->assertEquals('baz', $baz1->getName());
        $this->assertEquals(0755, $baz1->getPermissions());
        $baz2 = $foo->getChild('bar/baz');
        $this->assertSame($baz1, $baz2);
    }
}
?>