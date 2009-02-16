<?php
/**
 * Test for org::bovigo::vfs::vfsStreamDirectory.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 * @version     $Id$
 */
require_once 'org/bovigo/vfs/vfsStreamDirectory.php';
require_once 'PHPUnit/Framework.php';
/**
 * Test for org::bovigo::vfs::vfsStreamDirectory.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
class vfsStreamDirectoryTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @var  vfsStreamDirectory
     */
    protected $dir;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->dir = new vfsStreamDirectory('foo');
    }

    /**
     * assure that a directory seperator inside the name throws an exception
     *
     * @test
     * @expectedException  vfsStreamException
     */
    public function invalidCharacterInName()
    {
        $dir = new vfsStreamDirectory('foo/bar');
    }

    /**
     * test default values and methods
     *
     * @test
     */
    public function defaultValues()
    {
        $this->assertEquals(vfsStreamContent::TYPE_DIR, $this->dir->getType());
        $this->assertEquals('foo', $this->dir->getName());
        $this->assertTrue($this->dir->appliesTo('foo'));
        $this->assertTrue($this->dir->appliesTo('foo/bar'));
        $this->assertFalse($this->dir->appliesTo('bar'));
        $this->assertEquals(array(), $this->dir->getChildren());
    }

    /**
     * test renaming the directory
     *
     * @test
     */
    public function rename()
    {
        $this->dir->rename('bar');
        $this->assertEquals('bar', $this->dir->getName());
        $this->assertFalse($this->dir->appliesTo('foo'));
        $this->assertFalse($this->dir->appliesTo('foo/bar'));
        $this->assertTrue($this->dir->appliesTo('bar'));
    }

    /**
     * renaming the directory to an invalid name throws a vfsStreamException
     *
     * @test
     * @expectedException  vfsStreamException
     */
    public function renameToInvalidNameThrowsvfsStreamException()
    {
        $this->dir->rename('foo/baz');
    }

    /**
     * test checking and retrieving a non existing child
     *
     * @test
     */
    public function nonExistingChild()
    {
        $this->assertFalse($this->dir->hasChild('bar'));
        $this->assertNull($this->dir->getChild('bar'));
        $this->assertFalse($this->dir->removeChild('bar'));
        $mockChild = $this->getMock('vfsStreamContent');
        $mockChild->expects($this->any())
                  ->method('appliesTo')
                  ->will($this->returnValue(false));
        $mockChild->expects($this->any())
                  ->method('getName')
                  ->will($this->returnValue('baz'));
        $this->dir->addChild($mockChild);
        $this->assertFalse($this->dir->removeChild('bar'));
    }

    /**
     * test that adding, handling and removing of a child works as expected
     *
     * @test
     */
    public function childHandling()
    {
        $mockChild = $this->getMock('vfsStreamContent');
        $mockChild->expects($this->any())
                  ->method('getType')
                  ->will($this->returnValue(vfsStreamContent::TYPE_FILE));
        $mockChild->expects($this->any())
                  ->method('getName')
                  ->will($this->returnValue('bar'));
        $mockChild->expects($this->any())
                  ->method('appliesTo')
                  ->with($this->equalTo('bar'))
                  ->will($this->returnValue(true));
        $mockChild->expects($this->once())
                  ->method('size')
                  ->will($this->returnValue(5));
        $this->dir->addChild($mockChild);
        $this->assertTrue($this->dir->hasChild('bar'));
        $bar = $this->dir->getChild('bar');
        $this->assertSame($mockChild, $bar);
        $this->assertEquals(array($mockChild), $this->dir->getChildren());
        $this->assertEquals(0, $this->dir->size());
        $this->assertEquals(5, $this->dir->sizeSummarized());
        $this->assertTrue($this->dir->removeChild('bar'));
        $this->assertEquals(array(), $this->dir->getChildren());
        $this->assertEquals(0, $this->dir->size());
        $this->assertEquals(0, $this->dir->sizeSummarized());
    }

    /**
     * test that adding, handling and removing of a child works as expected
     *
     * @test
     */
    public function childHandlingWithSubdirectory()
    {
        $mockChild = $this->getMock('vfsStreamContent');
        $mockChild->expects($this->any())
                  ->method('getType')
                  ->will($this->returnValue(vfsStreamContent::TYPE_FILE));
        $mockChild->expects($this->any())
                  ->method('getName')
                  ->will($this->returnValue('bar'));
        $mockChild->expects($this->once())
                  ->method('size')
                  ->will($this->returnValue(5));
        $subdir = new vfsStreamDirectory('subdir');
        $subdir->addChild($mockChild);
        $this->dir->addChild($subdir);
        $this->assertTrue($this->dir->hasChild('subdir'));
        $this->assertSame($subdir, $this->dir->getChild('subdir'));
        $this->assertEquals(array($subdir), $this->dir->getChildren());
        $this->assertEquals(0, $this->dir->size());
        $this->assertEquals(5, $this->dir->sizeSummarized());
        $this->assertTrue($this->dir->removeChild('subdir'));
        $this->assertEquals(array(), $this->dir->getChildren());
        $this->assertEquals(0, $this->dir->size());
        $this->assertEquals(0, $this->dir->sizeSummarized());
    }
}
?>