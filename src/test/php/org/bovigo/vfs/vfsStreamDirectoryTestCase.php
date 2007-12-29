<?php
/**
 * Test for org::bovigo::vfs::vfsStreamDirectory.
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo_vfs
 * @subpackage  test
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
     * test that correct directory structure is created
     *
     * @test
     */
    public function create()
    {
        $foo = vfsStreamDirectory::create('foo/bar/baz');
        $this->assertEquals('foo', $foo->getName());
        $this->assertTrue($foo->hasChild('bar'));
        $this->assertTrue($foo->hasChild('bar/baz'));
        $this->assertFalse($foo->hasChild('baz'));
        $bar = $foo->getChild('bar');
        $this->assertEquals('bar', $bar->getName());
        $this->assertTrue($bar->hasChild('baz'));
        $baz1 = $bar->getChild('baz');
        $this->assertEquals('baz', $baz1->getName());
        $baz2 = $foo->getChild('bar/baz');
        $this->assertSame($baz1, $baz2);
    }

    /**
     * test that correct directory structure is created
     *
     * @test
     */
    public function createWithSlashAtStart()
    {
        $foo = vfsStreamDirectory::create('/foo/bar/baz');
        $this->assertEquals('foo', $foo->getName());
        $this->assertTrue($foo->hasChild('bar'));
        $this->assertTrue($foo->hasChild('bar/baz'));
        $this->assertFalse($foo->hasChild('baz'));
        $bar = $foo->getChild('bar');
        $this->assertEquals('bar', $bar->getName());
        $this->assertTrue($bar->hasChild('baz'));
        $baz1 = $bar->getChild('baz');
        $this->assertEquals('baz', $baz1->getName());
        $baz2 = $foo->getChild('bar/baz');
        $this->assertSame($baz1, $baz2);
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
        $this->assertEquals(5, $this->dir->size());
        $this->assertTrue($this->dir->removeChild('bar'));
        $this->assertEquals(array(), $this->dir->getChildren());
        $this->assertEquals(0, $this->dir->size());
    }

    /**
     * test method to be used for iterating
     *
     * @test
     */
    public function iteration()
    {
        $mockChild1 = $this->getMock('vfsStreamContent');
        $mockChild1->expects($this->any())
                   ->method('getName')
                   ->will($this->returnValue('bar'));
        $this->dir->addChild($mockChild1);
        $mockChild2 = $this->getMock('vfsStreamContent');
        $mockChild2->expects($this->any())
                   ->method('getName')
                   ->will($this->returnValue('baz'));
        $this->dir->addChild($mockChild2);
        $this->assertEquals('bar', $this->dir->key());
        $this->assertTrue($this->dir->valid());
        $bar = $this->dir->current();
        $this->assertSame($mockChild1, $bar);
        $this->dir->next();
        $this->assertEquals('baz', $this->dir->key());
        $this->assertTrue($this->dir->valid());
        $baz = $this->dir->current();
        $this->assertSame($mockChild2, $baz);
        $this->dir->next();
        $this->assertFalse($this->dir->valid());
        $this->assertNull($this->dir->key());
        $this->assertNull($this->dir->current());
        $this->dir->rewind();
        $this->assertTrue($this->dir->valid());
        $this->assertEquals('bar', $this->dir->key());
        $bar2 = $this->dir->current();
        $this->assertSame($mockChild1, $bar2);
    }
}
?>