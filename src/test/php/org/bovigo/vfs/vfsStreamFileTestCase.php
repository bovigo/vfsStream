<?php
/**
 * Test for org::stubbles::vfs::vfsStreamFile.
 *
 * @author      Frank Kleine <mikey@stubbles.net>
 * @package     stubbles_vfs
 * @subpackage  test
 */
require_once SRC_PATH . '/main/php/org/stubbles/vfs/vfsStreamFile.php';
/**
 * Test for org::stubbles::vfs::vfsStreamFile.
 *
 * @package     stubbles_vfs
 * @subpackage  test
 */
class vfsStreamFileTest extends UnitTestCase
{
    /**
     * instance to test
     *
     * @var  vfsStreamFile
     */
    protected $file;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->file = new vfsStreamFile('foo');
    }

    /**
     * test default values and methods
     */
    public function testDefaultValues()
    {
        $this->assertEqual($this->file->getType(), vfsStreamContent::TYPE_FILE);
        $this->assertEqual($this->file->getName(), 'foo');
        $this->assertTrue($this->file->appliesTo('foo'));
        $this->assertFalse($this->file->appliesTo('foo/bar'));
        $this->assertFalse($this->file->appliesTo('bar'));
        $this->assertFalse($this->file->hasChild('bar'));
    }

    /**
     * test setting and getting the content of a file
     */
    public function testContent()
    {
        $this->assertNull($this->file->getContent());
        $this->assertReference($this->file, $this->file->setContent('bar'));
        $this->assertEqual('bar', $this->file->getContent());
        $this->assertReference($this->file, $this->file->withContent('baz'));
        $this->assertEqual('baz', $this->file->getContent());
    }

    /**
     * test renaming the directory
     */
    public function testRename()
    {
        $this->file->rename('bar');
        $this->assertEqual($this->file->getName(), 'bar');
        $this->assertFalse($this->file->appliesTo('foo'));
        $this->assertFalse($this->file->appliesTo('foo/bar'));
        $this->assertTrue($this->file->appliesTo('bar'));
    }

    /**
     * test reading contents from the file
     */
    public function testReadEmptyFile()
    {
        $this->assertTrue($this->file->eof());
        $this->assertEqual($this->file->size(), 0);
        $this->assertEqual($this->file->read(5), '');
        $this->assertEqual($this->file->getBytesRead(), 5);
        $this->assertTrue($this->file->eof());
    }

    /**
     * test reading contents from the file
     */
    public function testRead()
    {
        $this->file->setContent('foobarbaz');
        $this->assertFalse($this->file->eof());
        $this->assertEqual($this->file->size(), 9);
        $this->assertEqual($this->file->read(3), 'foo');
        $this->assertEqual($this->file->getBytesRead(), 3);
        $this->assertFalse($this->file->eof());
        $this->assertEqual($this->file->size(), 9);
        $this->assertEqual($this->file->read(3), 'bar');
        $this->assertEqual($this->file->getBytesRead(), 6);
        $this->assertFalse($this->file->eof());
        $this->assertEqual($this->file->size(), 9);
        $this->assertEqual($this->file->read(3), 'baz');
        $this->assertEqual($this->file->getBytesRead(), 9);
        $this->assertTrue($this->file->eof());
        $this->assertEqual($this->file->read(3), '');
    }

    /**
     * test seeking to offset
     */
    public function testSeekEmptyFile()
    {
        $this->assertFalse($this->file->seek(0, 55));
        $this->assertTrue($this->file->seek(0, SEEK_SET));
        $this->assertEqual($this->file->getBytesRead(), 0);
        $this->assertTrue($this->file->seek(5, SEEK_SET));
        $this->assertEqual($this->file->getBytesRead(), 5);
        $this->assertTrue($this->file->seek(0, SEEK_CUR));
        $this->assertEqual($this->file->getBytesRead(), 5);
        $this->assertTrue($this->file->seek(2, SEEK_CUR));
        $this->assertEqual($this->file->getBytesRead(), 7);
        $this->assertTrue($this->file->seek(0, SEEK_END));
        $this->assertEqual($this->file->getBytesRead(), 0);
        $this->assertTrue($this->file->seek(2, SEEK_END));
        $this->assertEqual($this->file->getBytesRead(), 2);
    }

    /**
     * test seeking to offset
     */
    public function testSeekRead()
    {
        $this->file->setContent('foobarbaz');
        $this->assertFalse($this->file->seek(0, 55));
        $this->assertTrue($this->file->seek(0, SEEK_SET));
        $this->assertEqual($this->file->readUntilEnd(), 'foobarbaz');
        $this->assertEqual($this->file->getBytesRead(), 0);
        $this->assertTrue($this->file->seek(5, SEEK_SET));
        $this->assertEqual($this->file->readUntilEnd(), 'rbaz');
        $this->assertEqual($this->file->getBytesRead(), 5);
        $this->assertTrue($this->file->seek(0, SEEK_CUR));
        $this->assertEqual($this->file->readUntilEnd(), 'rbaz');
        $this->assertEqual($this->file->getBytesRead(), 5);
        $this->assertTrue($this->file->seek(2, SEEK_CUR));
        $this->assertEqual($this->file->readUntilEnd(), 'az');
        $this->assertEqual($this->file->getBytesRead(), 7);
        $this->assertTrue($this->file->seek(0, SEEK_END));
        $this->assertEqual($this->file->readUntilEnd(), '');
        $this->assertEqual($this->file->getBytesRead(), 9);
        $this->assertTrue($this->file->seek(2, SEEK_END));
        $this->assertEqual($this->file->readUntilEnd(), '');
        $this->assertEqual($this->file->getBytesRead(), 11);
    }

    /**
     * test writing data into the file
     */
    public function testWriteEmptyFile()
    {
        $this->assertEqual($this->file->write('foo'), 3);
        $this->assertEqual($this->file->getContent(), 'foo');
        $this->assertEqual($this->file->size(), 3);
        $this->assertEqual($this->file->write('bar'), 3);
        $this->assertEqual($this->file->getContent(), 'foobar');
        $this->assertEqual($this->file->size(), 6);
    }

    /**
     * test writing data into the file
     */
    public function testWrite()
    {
        $this->file->setContent('foobarbaz');
        $this->assertTrue($this->file->seek(3, SEEK_SET));
        $this->assertEqual($this->file->write('foo'), 3);
        $this->assertEqual($this->file->getContent(), 'foofoobaz');
        $this->assertEqual($this->file->size(), 9);
        $this->assertEqual($this->file->write('bar'), 3);
        $this->assertEqual($this->file->getContent(), 'foofoobar');
        $this->assertEqual($this->file->size(), 9);
    }
}
?>