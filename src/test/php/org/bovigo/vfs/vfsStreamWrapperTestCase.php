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
require_once dirname(__FILE__) . '/vfsStreamWrapperBaseTestCase.php';
/**
 * Test for org::bovigo::vfs::vfsStreamWrapper.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
class vfsStreamWrapperTestCase extends vfsStreamWrapperBaseTestCase
{
    /**
     * ensure that a call to vfsStreamWrapper::register() resets the stream
     * 
     * Implemented after a request by David Zülke.
     *
     * @test
     */
    public function resetByRegister()
    {
        $this->assertSame($this->foo, vfsStreamWrapper::getRoot());
        vfsStreamWrapper::register();
        $this->assertNull(vfsStreamWrapper::getRoot());
    }

    /**
     * assure that filesize is returned correct
     *
     * @test
     */
    public function filesize()
    {
        $this->assertEquals(0, filesize($this->fooURL));
        $this->assertEquals(0, filesize($this->barURL));
        $this->assertEquals(4, filesize($this->baz2URL));
        $this->assertEquals(5, filesize($this->baz1URL));
    }

    /**
     * assert that file_exists() delivers correct result
     *
     * @test
     */
    public function file_exists()
    {
        $this->assertTrue(file_exists($this->fooURL));
        $this->assertTrue(file_exists($this->barURL));
        $this->assertTrue(file_exists($this->baz1URL));
        $this->assertTrue(file_exists($this->baz2URL));
        $this->assertFalse(file_exists($this->fooURL . '/another'));
        $this->assertFalse(file_exists(vfsStream::url('another')));
    }

    /**
     * assert that filemtime() delivers correct result
     *
     * @test
     */
    public function filemtime()
    {
        $this->assertEquals(100, filemtime($this->fooURL));
        $this->assertEquals(200, filemtime($this->barURL));
        $this->assertEquals(300, filemtime($this->baz1URL));
        $this->assertEquals(400, filemtime($this->baz2URL));
    }

    /**
     * assert that unlink() removes files and directories
     *
     * @test
     */
    public function unlink()
    {
        $this->assertTrue(unlink($this->baz2URL));
        $this->assertFalse(file_exists($this->baz2URL)); // make sure statcache was cleared
        $this->assertEquals(array($this->bar), $this->foo->getChildren());
        $this->assertTrue(unlink($this->barURL));
        $this->assertFalse(file_exists($this->barURL)); // make sure statcache was cleared
        $this->assertEquals(array(), $this->foo->getChildren());
        $this->assertFalse(unlink($this->fooURL . '/another'));
        $this->assertFalse(unlink(vfsStream::url('another')));
        $this->assertEquals(array(), $this->foo->getChildren());
        $this->assertTrue(unlink($this->fooURL));
        $this->assertFalse(file_exists($this->fooURL)); // make sure statcache was cleared
        $this->assertNull(vfsStreamWrapper::getRoot());
    }

    /**
     * assert dirname() returns correct directory name
     *
     * @test
     */
    public function dirname()
    {
        $this->assertEquals($this->fooURL, dirname($this->barURL));
        $this->assertEquals($this->barURL, dirname($this->baz1URL));
        # returns "vfs:" instead of "."
        # however this seems not to be fixable because dirname() does not
        # call the stream wrapper
        #$this->assertEquals(dirname(vfsStream::url('doesNotExist')), '.');
    }

    /**
     * assert basename() returns correct file name
     *
     * @test
     */
    public function basename()
    {
        $this->assertEquals('bar', basename($this->barURL));
        $this->assertEquals('baz1', basename($this->baz1URL));
        $this->assertEquals('doesNotExist', basename(vfsStream::url('doesNotExist')));
    }

    /**
     * assert is_readable() returns always true for existing pathes
     *
     * As long as file mode is not supported, existing pathes will lead to true,
     * and non-existing pathes to false.
     *
     * @test
     */
    public function is_readable()
    {
        $this->assertTrue(is_readable($this->fooURL));
        $this->assertTrue(is_readable($this->barURL));
        $this->assertTrue(is_readable($this->baz1URL));
        $this->assertTrue(is_readable($this->baz2URL));
        $this->assertFalse(is_readable($this->fooURL . '/another'));
        $this->assertFalse(is_readable(vfsStream::url('another')));
    }
}
?>