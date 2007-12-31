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
class vfsStreamWrapperFileTestCase extends vfsStreamWrapperBaseTestCase
{
    /**
     * assert that file_get_contents() delivers correct file contents
     *
     * @test
     */
    public function file_get_contents()
    {
        $this->assertEquals('baz2', file_get_contents($this->baz2URL));
        $this->assertEquals('baz 1', file_get_contents($this->baz1URL));
        $this->assertFalse(@file_get_contents($this->barURL));
        $this->assertFalse(@file_get_contents($this->fooURL));
    }

    /**
     * assert that file_put_contents() delivers correct file contents
     *
     * @test
     */
    public function file_put_contentsExistingFile()
    {
        $this->assertEquals(14, file_put_contents($this->baz2URL, 'baz is not bar'));
        $this->assertEquals('baz is not bar', $this->baz2->getContent());
        $this->assertEquals(6, file_put_contents($this->baz1URL, 'foobar'));
        $this->assertEquals('foobar', $this->baz1->getContent());
        $this->assertFalse(@file_put_contents($this->barURL, 'This does not work.'));
        $this->assertFalse(@file_put_contents($this->fooURL, 'This does not work, too.'));
    }

    /**
     * assert that file_put_contents() delivers correct file contents
     *
     * @test
     */
    public function file_put_contentsNonExistingFile()
    {
        $this->assertEquals(14, file_put_contents($this->fooURL . '/baznot.bar', 'baz is not bar'));
        $this->assertEquals(3, count($this->foo->getChildren()));
        $this->assertEquals(14, file_put_contents($this->barURL . '/baznot.bar', 'baz is not bar'));
        $this->assertEquals(2, count($this->bar->getChildren()));
    }

    /**
     * using a file pointer should work without any problems
     *
     * @test
     */
    public function usingFilePointer()
    {
        $fp = fopen($this->baz1URL, 'r');
        $this->assertEquals(0, ftell($fp));
        $this->assertFalse(feof($fp));
        $this->assertEquals(0, fseek($fp, 2));
        $this->assertEquals(2, ftell($fp));
        $this->assertEquals(0, fseek($fp, 1, SEEK_CUR));
        $this->assertEquals(3, ftell($fp));
        $this->assertEquals(0, fseek($fp, 1, SEEK_END));
        $this->assertEquals(6, ftell($fp));
        $this->assertTrue(feof($fp));
        $this->assertEquals(0, fseek($fp, 2));
        $this->assertFalse(feof($fp));
        $this->assertEquals(2, ftell($fp));
        $this->assertEquals('z', fread($fp, 1));
        $this->assertEquals(3, ftell($fp));
        $this->assertEquals(' 1', fread($fp, 8092));
        $this->assertEquals(5, ftell($fp));
        $this->assertTrue(fclose($fp));
    }

    /**
     * assert is_file() returns correct result
     *
     * @test
     */
    public function is_file()
    {
        $this->assertFalse(is_file($this->fooURL));
        $this->assertFalse(is_file($this->barURL));
        $this->assertTrue(is_file($this->baz1URL));
        $this->assertTrue(is_file($this->baz2URL));
        $this->assertFalse(is_readable($this->fooURL . '/another'));
        $this->assertFalse(is_readable(vfsStream::url('another')));
    }
}
?>