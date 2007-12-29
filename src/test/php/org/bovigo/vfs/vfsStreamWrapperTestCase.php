<?php
/**
 * Test for org::stubbles::vfs::vfsStreamWrapper.
 *
 * @author      Frank Kleine <mikey@stubbles.net>
 * @package     stubbles_vfs
 * @subpackage  test
 */
require_once SRC_PATH . '/main/php/org/stubbles/vfs/vfsStream.php';
Mock::generate('vfsStreamContent');
/**
 * Test for org::stubbles::vfs::vfsStreamWrapper.
 *
 * @package     stubbles_vfs
 * @subpackage  test
 */
class vfsStreamWrapperTestCase extends UnitTestCase
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
        $this->baz1    = new vfsStreamFile('baz1');
        $this->baz1->setContent('baz 1');
        $this->baz1->setFilemtime(300);
        $this->baz2    = new vfsStreamFile('baz2');
        $this->baz2->setContent('baz2');
        $this->baz2->setFilemtime(400);
        $this->bar->addChild($this->baz1);
        $this->foo->addChild($this->bar);
        $this->foo->addChild($this->baz2);
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot($this->foo);
    }

    /**
     * ensure that a call to vfsStreamWrapper::register() resets the stream
     * 
     * Implemented because of a hint by David Zülke.
     */
    public function testResetByRegister()
    {
        $this->assertReference($this->foo, vfsStreamWrapper::getRoot());
        vfsStreamWrapper::register();
        $this->assertNull(vfsStreamWrapper::getRoot());
    }

    /**
     * assure that a directory iteration works as expected
     */
    public function testDirectoryIteration()
    {
        $dir = dir($this->fooURL);
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            $this->assertTrue('bar' === $entry || 'baz2' === $entry);
        }
        
        $this->assertEqual($i, 2, 'Directory foo contains two children, but got ' . $i . ' children while iterating over directory contents');
        $dir->close();
    }

    /**
     * assure that filesize is returned correct
     */
    public function testFilesize()
    {
        $this->assertEqual(filesize($this->fooURL), 0);
        $this->assertEqual(filesize($this->barURL), 0);
        $this->assertEqual(filesize($this->baz2URL), 4);
        $this->assertEqual(filesize($this->baz1URL), 5);
    }

    /**
     * assert that file_get_contents() delivers correct file contents
     */
    public function testFile_get_contents()
    {
        $this->assertEqual(file_get_contents($this->baz2URL), 'baz2');
        $this->assertEqual(file_get_contents($this->baz1URL), 'baz 1');
        $this->assertFalse(@file_get_contents($this->barURL));
        $this->assertFalse(@file_get_contents($this->fooURL));
    }

    /**
     * assert that file_put_contents() delivers correct file contents
     */
    public function testFile_put_contentsExistingFile()
    {
        $this->assertEqual(file_put_contents($this->baz2URL, 'baz is not bar'), 14);
        $this->assertEqual($this->baz2->getContent(), 'baz is not bar');
        $this->assertEqual(file_put_contents($this->baz1URL, 'foobar'), 6);
        $this->assertEqual($this->baz1->getContent(), 'foobar');
        $this->assertFalse(@file_put_contents($this->barURL, 'This does not work.'));
        $this->assertFalse(@file_put_contents($this->fooURL, 'This does not work, too.'));
    }

    /**
     * assert that file_put_contents() delivers correct file contents
     */
    public function testFile_put_contentsNonExistingFile()
    {
        $this->assertEqual(file_put_contents($this->fooURL . '/baznot.bar', 'baz is not bar'), 14);
        $this->assertEqual(count($this->foo->getChildren()), 3);
        $this->assertEqual(file_put_contents($this->barURL . '/baznot.bar', 'baz is not bar'), 14);
        $this->assertEqual(count($this->bar->getChildren()), 2);
    }

    /**
     * assert that file_exists() delivers correct result
     */
    public function testFile_exists()
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
     */
    public function testFilemtime()
    {
        $this->assertEqual(filemtime($this->fooURL), 100);
        $this->assertEqual(filemtime($this->barURL), 200);
        $this->assertEqual(filemtime($this->baz1URL), 300);
        $this->assertEqual(filemtime($this->baz2URL), 400);
    }

    /**
     * assert that unlink() removes files and directories
     */
    public function testUnlink()
    {
        $this->assertTrue(unlink($this->baz2URL));
        $this->assertFalse(file_exists($this->baz2URL)); // make sure statcache was cleared
        $this->assertEqual($this->foo->getChildren(), array($this->bar));
        $this->assertTrue(unlink($this->barURL));
        $this->assertFalse(file_exists($this->barURL)); // make sure statcache was cleared
        $this->assertEqual($this->foo->getChildren(), array());
        $this->assertFalse(unlink($this->fooURL . '/another'));
        $this->assertFalse(unlink(vfsStream::url('another')));
        $this->assertEqual($this->foo->getChildren(), array());
        $this->assertTrue(unlink($this->fooURL));
        $this->assertFalse(file_exists($this->fooURL)); // make sure statcache was cleared
        $this->assertNull(vfsStreamWrapper::getRoot());
    }

    /**
     * assert that mkdir() creates the correct directory structure
     */
    public function testMkdirNoNewRoot()
    {
        $this->assertFalse(mkdir(vfsStream::url('another')));
        $this->assertEqual(count($this->foo->getChildren()), 2);
        $this->assertReference($this->foo, vfsStreamWrapper::getRoot());
    }

    /**
     * assert that mkdir() creates the correct directory structure
     */
    public function testMkdirNonRecursively()
    {
        $this->assertFalse(mkdir($this->barURL . '/another/more'));
        $this->assertEqual(count($this->foo->getChildren()), 2);
        $this->assertTrue(mkdir($this->fooURL . '/another'));
        $this->assertEqual(count($this->foo->getChildren()), 3);
    }

    /**
     * assert that mkdir() creates the correct directory structure
     */
    public function testMkdirRecursively()
    {
        $this->assertTrue(mkdir($this->fooURL . '/another/more', 0700, true));
        $this->assertEqual(count($this->foo->getChildren()), 3);
        $another = $this->foo->getChild('another');
        $this->assertTrue($another->hasChild('more'));
    }

    /**
     * assert dirname() returns correct directory name
     */
    public function testDirname()
    {
        $this->assertEqual(dirname($this->barURL), $this->fooURL);
        $this->assertEqual(dirname($this->baz1URL), $this->barURL);
        # returns "vfs:" instead of "."
        # however this seems not to be fixable because dirname() does not
        # call the stream wrapper
        #$this->assertEqual(dirname(vfsStream::url('doesNotExist')), '.');
    }

    /**
     * assert basename() returns correct file name
     */
    public function testBasename()
    {
        $this->assertEqual(basename($this->barURL), 'bar');
        $this->assertEqual(basename($this->baz1URL), 'baz1');
        $this->assertEqual(basename(vfsStream::url('doesNotExist')), 'doesNotExist');
    }

    /**
     * assert is_readable() returns always true for existing pathes
     *
     * As long as file mode is not supported, existing pathes will lead to true,
     * and non-existing pathes to false.
     */
    public function testIs_readable()
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
     */
    public function testIs_dir()
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
     */
    public function testIs_file()
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