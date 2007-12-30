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
class vfsStreamWrapperTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * root directory
     *
     * @var  vfsStreamDirectory
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
     * @var  vfsStreamDirectory
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
     * @var  vfsStreamFile
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
     * @var  vfsStreamFile
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
        $this->foo     = new vfsStreamDirectory('foo');
        $this->foo->setFilemtime(100);
        $this->bar     = new vfsStreamDirectory('bar');
        $this->bar->setFilemtime(200);
        $this->baz1    = vfsStream::newFile('baz1')->lastModified(300)->withContent('baz 1');
        $this->baz2    = vfsStream::newFile('baz2')->withContent('baz2')->setFilemtime(400);
        $this->bar->addChild($this->baz1);
        $this->foo->addChild($this->bar);
        $this->foo->addChild($this->baz2);
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot($this->foo);
    }

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
     * assure that a directory iteration works as expected
     *
     * @test
     */
    public function directoryIteration()
    {
        $dir = dir($this->fooURL);
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            $this->assertTrue('bar' === $entry || 'baz2' === $entry);
        }
        
        $this->assertEquals(2, $i, 'Directory foo contains two children, but got ' . $i . ' children while iterating over directory contents');
        $dir->rewind();
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            $this->assertTrue('bar' === $entry || 'baz2' === $entry);
        }
        
        $this->assertEquals(2, $i, 'Directory foo contains two children, but got ' . $i . ' children while iterating over directory contents');
        $dir->close();
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
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     */
    public function mkdirNoNewRoot()
    {
        $this->assertFalse(mkdir(vfsStream::url('another')));
        $this->assertEquals(2, count($this->foo->getChildren()));
        $this->assertSame($this->foo, vfsStreamWrapper::getRoot());
    }

    /**
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     */
    public function mkdirNonRecursively()
    {
        $this->assertFalse(mkdir($this->barURL . '/another/more'));
        $this->assertEquals(2, count($this->foo->getChildren()));
        $this->assertTrue(mkdir($this->fooURL . '/another'));
        $this->assertEquals(3, count($this->foo->getChildren()));
    }

    /**
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     */
    public function mkdirRecursively()
    {
        $this->assertTrue(mkdir($this->fooURL . '/another/more', 0700, true));
        $this->assertEquals(3, count($this->foo->getChildren()));
        $another = $this->foo->getChild('another');
        $this->assertTrue($another->hasChild('more'));
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

    /**
     * assert is_dir() returns correct result
     *
     * @test
     */
    public function is_dir()
    {
        $this->assertTrue(is_dir($this->fooURL));
        $this->assertTrue(is_dir($this->barURL));
        $this->assertFalse(is_dir($this->baz1URL));
        $this->assertFalse(is_dir($this->baz2URL));
        $this->assertFalse(is_dir($this->fooURL . '/another'));
        $this->assertFalse(is_dir(vfsStream::url('another')));
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