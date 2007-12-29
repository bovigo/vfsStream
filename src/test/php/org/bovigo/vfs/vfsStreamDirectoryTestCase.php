<?php
/**
 * Test for org::stubbles::vfs::vfsStreamDirectory.
 *
 * @author      Frank Kleine <mikey@stubbles.net>
 * @package     stubbles_vfs
 * @subpackage  test
 */
require_once SRC_PATH . '/main/php/org/stubbles/vfs/vfsStreamDirectory.php';
Mock::generate('vfsStreamContent');
/**
 * Test for org::stubbles::vfs::vfsStreamDirectory.
 *
 * @package     stubbles_vfs
 * @subpackage  test
 */
class vfsStreamDirectoryTestCase extends UnitTestCase
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
     */
    public function testInvalidCharacterInName()
    {
        $this->expectException('vfsStreamException');
        $dir = new vfsStreamDirectory('foo/bar');
    }

    /**
     * test default values and methods
     */
    public function testDefaultValues()
    {
        $this->assertEqual($this->dir->getType(), vfsStreamContent::TYPE_DIR);
        $this->assertEqual($this->dir->getName(), 'foo');
        $this->assertTrue($this->dir->appliesTo('foo'));
        $this->assertTrue($this->dir->appliesTo('foo/bar'));
        $this->assertFalse($this->dir->appliesTo('bar'));
        $this->assertEqual($this->dir->getChildren(), array());
    }

    /**
     * test renaming the directory
     */
    public function testRename()
    {
        $this->dir->rename('bar');
        $this->assertEqual($this->dir->getName(), 'bar');
        $this->assertFalse($this->dir->appliesTo('foo'));
        $this->assertFalse($this->dir->appliesTo('foo/bar'));
        $this->assertTrue($this->dir->appliesTo('bar'));
        
        $this->expectException('vfsStreamException');
        $this->dir->rename('foo/baz');
    }

    /**
     * test that correct directory structure is created
     */
    public function testCreate()
    {
        $foo = vfsStreamDirectory::create('foo/bar/baz');
        $this->assertEqual($foo->getName(), 'foo');
        $this->assertTrue($foo->hasChild('bar'));
        $this->assertTrue($foo->hasChild('bar/baz'));
        $this->assertFalse($foo->hasChild('baz'));
        $bar = $foo->getChild('bar');
        $this->assertEqual($bar->getName(), 'bar');
        $this->assertTrue($bar->hasChild('baz'));
        $baz1 = $bar->getChild('baz');
        $this->assertEqual($baz1->getName(), 'baz');
        $baz2 = $foo->getChild('bar/baz');
        $this->assertReference($baz1, $baz2);
    }

    /**
     * test checking and retrieving a non existing child
     */
    public function testNonExistingChild()
    {
        $this->assertFalse($this->dir->hasChild('bar'));
        $this->assertNull($this->dir->getChild('bar'));
        $this->assertFalse($this->dir->removeChild('bar'));
    }

    /**
     * test that adding, handling and removing of a child works as expected
     */
    public function testChildHandling()
    {
        $mockChild = new MockvfsStreamContent();
        $mockChild->setReturnValue('getName', 'bar');
        $this->dir->addChild($mockChild);
        $this->assertTrue($this->dir->hasChild('bar'));
        $bar = $this->dir->getChild('bar');
        $this->assertReference($mockChild, $bar);
        $this->assertEqual($this->dir->getChildren(), array($mockChild));
        $this->assertTrue($this->dir->removeChild('bar'));
        $this->assertEqual($this->dir->getChildren(), array());
    }

    /**
     * test method to be used for iterating
     */
    public function testIteration()
    {
        $mockChild1 = new MockvfsStreamContent();
        $mockChild1->setReturnValue('getName', 'bar');
        $this->dir->addChild($mockChild1);
        $mockChild2 = new MockvfsStreamContent();
        $mockChild2->setReturnValue('getName', 'baz');
        $this->dir->addChild($mockChild2);
        $this->assertEqual($this->dir->key(), 'bar');
        $this->assertTrue($this->dir->valid());
        $bar = $this->dir->current();
        $this->assertReference($mockChild1, $bar);
        $this->dir->next();
        $this->assertEqual($this->dir->key(), 'baz');
        $this->assertTrue($this->dir->valid());
        $baz = $this->dir->current();
        $this->assertReference($mockChild2, $baz);
        $this->dir->next();
        $this->assertFalse($this->dir->valid());
        $this->assertNull($this->dir->key());
        $this->assertNull($this->dir->current());
        $this->dir->rewind();
        $this->assertTrue($this->dir->valid());
        $this->assertEqual($this->dir->key(), 'bar');
        $bar2 = $this->dir->current();
        $this->assertReference($mockChild1, $bar2);
    }
}
?>