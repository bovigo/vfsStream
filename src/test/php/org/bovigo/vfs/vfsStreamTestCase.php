<?php
/**
 * Test for org::bovigo::vfs::vfsStream.
 *
 * @package     bovigo_vfs
 * @subpackage  test
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
     * set up test environment
     */
    public function setUp()
    {
        vfsStreamWrapper::register();
    }

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
        $this->assertInstanceOf('vfsStreamFile', $file);
        $this->assertEquals('filename.txt', $file->getName());
        $this->assertEquals(0666, $file->getPermissions());
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
        $this->assertInstanceOf('vfsStreamFile', $file);
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

    /**
     * @test
     * @group  setup
     * @since  0.7.0
     */
    public function setupRegistersStreamWrapperAndCreatesRootDirectoryWithDefaultNameAndPermissions()
    {
        $root = vfsStream::setup();
        $this->assertSame($root, vfsStreamWrapper::getRoot());
        $this->assertEquals('root', $root->getName());
        $this->assertEquals(0777, $root->getPermissions());
    }

    /**
     * @test
     * @group  setup
     * @since  0.7.0
     */
    public function setupRegistersStreamWrapperAndCreatesRootDirectoryWithGivenNameAndDefaultPermissions()
    {
        $root = vfsStream::setup('foo');
        $this->assertSame($root, vfsStreamWrapper::getRoot());
        $this->assertEquals('foo', $root->getName());
        $this->assertEquals(0777, $root->getPermissions());
    }

    /**
     * @test
     * @group  setup
     * @since  0.7.0
     */
    public function setupRegistersStreamWrapperAndCreatesRootDirectoryWithGivenNameAndPermissions()
    {
        $root = vfsStream::setup('foo', 0444);
        $this->assertSame($root, vfsStreamWrapper::getRoot());
        $this->assertEquals('foo', $root->getName());
        $this->assertEquals(0444, $root->getPermissions());
    }

    /**
     * @test
     * @group  issue_14
     * @group  issue_20
     * @since  0.10.0
     */
    public function replaceWithEmptyArrayIsEqualToSetup()
    {
        $root = vfsStream::replace(array(), 'example', 0755);
        $this->assertEquals('example', $root->getName());
        $this->assertEquals(0755, $root->getPermissions());
        $this->assertFalse($root->hasChildren());
    }

    /**
     * @test
     * @group  issue_14
     * @group  issue_20
     * @since  0.10.0
     */
    public function replaceWithoutRootDataResultsInDefaultRoot()
    {
        $root = vfsStream::replace(array());
        $this->assertEquals('root', $root->getName());
        $this->assertEquals(0777, $root->getPermissions());
        $this->assertFalse($root->hasChildren());
    }

    /**
     * @test
     * @group  issue_14
     * @group  issue_20
     * @since  0.10.0
     */
    public function replaceArraysAreTurnedIntoSubdirectories()
    {
        $root = vfsStream::replace(array('test' => array()), 'example');
        $this->assertTrue($root->hasChildren());
        $this->assertTrue($root->hasChild('test'));
        $this->assertInstanceOf('vfsStreamDirectory',
                                $root->getChild('test')
        );
        $this->assertFalse($root->getChild('test')->hasChildren());
    }

    /**
     * @test
     * @group  issue_14
     * @group  issue_20
     * @since  0.10.0
     */
    public function replaceStringsAreTurnedIntoFilesWithContent()
    {
        $root = vfsStream::replace(array('test.txt' => 'some content'), 'example');
        $this->assertTrue($root->hasChildren());
        $this->assertTrue($root->hasChild('test.txt'));
        $this->assertVfsFile($root->getChild('test.txt'), 'some content');
    }

    /**
     * @test
     * @group  issue_14
     * @group  issue_20
     * @since  0.10.0
     */
    public function replaceWorksRecursively()
    {
        $root = vfsStream::replace(array('test' => array('foo'     => array('test.txt' => 'hello'),
                                                        'baz.txt' => 'world'
                                                  )
                                  ),
                                  'example');
        $this->assertTrue($root->hasChildren());
        $this->assertTrue($root->hasChild('test'));
        $test = $root->getChild('test');
        $this->assertInstanceOf('vfsStreamDirectory', $test);
        $this->assertTrue($test->hasChildren());
        $this->assertTrue($test->hasChild('baz.txt'));
        $this->assertVfsFile($test->getChild('baz.txt'), 'world');

        $this->assertTrue($test->hasChild('foo'));
        $foo = $test->getChild('foo');
        $this->assertInstanceOf('vfsStreamDirectory', $foo);
        $this->assertTrue($foo->hasChildren());
        $this->assertTrue($foo->hasChild('test.txt'));
        $this->assertVfsFile($foo->getChild('test.txt'), 'hello');
    }

    /**
    * @test
    * @group  issue_17
    * @group  issue_20
    */
    public function replaceCastsNumericDirectoriesToStrings()
    {
        $root = vfsStream::replace(array(2011 => array ('test.txt' => 'some content')));
        $this->assertTrue($root->hasChild('2011'));

        $directory = $root->getChild('2011');
        $this->assertVfsFile($directory->getChild('test.txt'), 'some content');

        $this->assertTrue(file_exists('vfs://2011/test.txt'));
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createArraysAreTurnedIntoSubdirectories()
    {
        $baseDir = vfsStream::create(array('test' => array()), new vfsStreamDirectory('baseDir'));
        $this->assertTrue($baseDir->hasChildren());
        $this->assertTrue($baseDir->hasChild('test'));
        $this->assertInstanceOf('vfsStreamDirectory',
                                $baseDir->getChild('test')
        );
        $this->assertFalse($baseDir->getChild('test')->hasChildren());
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createArraysAreTurnedIntoSubdirectoriesOfRoot()
    {
        $root = vfsStream::setup();
        $this->assertSame($root, vfsStream::create(array('test' => array())));
        $this->assertTrue($root->hasChildren());
        $this->assertTrue($root->hasChild('test'));
        $this->assertInstanceOf('vfsStreamDirectory',
                                $root->getChild('test')
        );
        $this->assertFalse($root->getChild('test')->hasChildren());
    }

    /**
     * @test
     * @group  issue_20
     * @expectedException  vfsStreamException
     * @since  0.11.0
     */
    public function createThrowsExceptionIfNoBaseDirGivenAndNoRootSet()
    {
        vfsStream::create(array('test' => array()));
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createWorksRecursively()
    {
        $baseDir = vfsStream::create(array('test' => array('foo'     => array('test.txt' => 'hello'),
                                                           'baz.txt' => 'world'
                                                     )
                                     ),
                                     new vfsStreamDirectory('baseDir')
                   );
        $this->assertTrue($baseDir->hasChildren());
        $this->assertTrue($baseDir->hasChild('test'));
        $test = $baseDir->getChild('test');
        $this->assertInstanceOf('vfsStreamDirectory', $test);
        $this->assertTrue($test->hasChildren());
        $this->assertTrue($test->hasChild('baz.txt'));
        $this->assertVfsFile($test->getChild('baz.txt'), 'world');

        $this->assertTrue($test->hasChild('foo'));
        $foo = $test->getChild('foo');
        $this->assertInstanceOf('vfsStreamDirectory', $foo);
        $this->assertTrue($foo->hasChildren());
        $this->assertTrue($foo->hasChild('test.txt'));
        $this->assertVfsFile($foo->getChild('test.txt'), 'hello');
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createWorksRecursivelyWithRoot()
    {
        $root = vfsStream::setup();
        $this->assertSame($root,
                          vfsStream::create(array('test' => array('foo'     => array('test.txt' => 'hello'),
                                                                  'baz.txt' => 'world'
                                                            )
                                            )
                          )
        );
        $this->assertTrue($root->hasChildren());
        $this->assertTrue($root->hasChild('test'));
        $test = $root->getChild('test');
        $this->assertInstanceOf('vfsStreamDirectory', $test);
        $this->assertTrue($test->hasChildren());
        $this->assertTrue($test->hasChild('baz.txt'));
        $this->assertVfsFile($test->getChild('baz.txt'), 'world');

        $this->assertTrue($test->hasChild('foo'));
        $foo = $test->getChild('foo');
        $this->assertInstanceOf('vfsStreamDirectory', $foo);
        $this->assertTrue($foo->hasChildren());
        $this->assertTrue($foo->hasChild('test.txt'));
        $this->assertVfsFile($foo->getChild('test.txt'), 'hello');
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.10.0
     */
    public function createStringsAreTurnedIntoFilesWithContent()
    {
        $baseDir = vfsStream::create(array('test.txt' => 'some content'), new vfsStreamDirectory('baseDir'));
        $this->assertTrue($baseDir->hasChildren());
        $this->assertTrue($baseDir->hasChild('test.txt'));
        $this->assertVfsFile($baseDir->getChild('test.txt'), 'some content');
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createStringsAreTurnedIntoFilesWithContentWithRoot()
    {
        $root = vfsStream::setup();
        $this->assertSame($root,
                          vfsStream::create(array('test.txt' => 'some content'))
        );
        $this->assertTrue($root->hasChildren());
        $this->assertTrue($root->hasChild('test.txt'));
        $this->assertVfsFile($root->getChild('test.txt'), 'some content');
    }

    /**
    * @test
    * @group  issue_20
    * @since  0.11.0
    */
    public function createCastsNumericDirectoriesToStrings()
    {
        $baseDir = vfsStream::create(array(2011 => array ('test.txt' => 'some content')), new vfsStreamDirectory('baseDir'));
        $this->assertTrue($baseDir->hasChild('2011'));

        $directory = $baseDir->getChild('2011');
        $this->assertVfsFile($directory->getChild('test.txt'), 'some content');
    }

    /**
    * @test
    * @group  issue_20
    * @since  0.11.0
    */
    public function createCastsNumericDirectoriesToStringsWithRoot()
    {
        $root = vfsStream::setup();
        $this->assertSame($root,
                          vfsStream::create(array(2011 => array ('test.txt' => 'some content')))
        );
        $this->assertTrue($root->hasChild('2011'));

        $directory = $root->getChild('2011');
        $this->assertVfsFile($directory->getChild('test.txt'), 'some content');
    }

    /**
     * helper function for assertions on vfsStreamFile
     *
     * @param  vfsStreamFile  $file
     * @param  string         $content
     */
    protected function assertVfsFile(vfsStreamFile $file, $content)
    {
        $this->assertInstanceOf('vfsStreamFile',
                                $file
        );
        $this->assertEquals($content,
                            $file->getContent()
        );
    }

    /**
     * @test
     * @group  issue_10
     * @since  0.10.0
     */
    public function inspectWithContentGivesContentToVisitor()
    {
        $mockContent = $this->getMock('vfsStreamContent');
        $mockVisitor = $this->getMock('vfsStreamVisitor');
        $mockVisitor->expects($this->once())
                    ->method('visit')
                    ->with($this->equalTo($mockContent))
                    ->will($this->returnValue($mockVisitor));
        $this->assertSame($mockVisitor, vfsStream::inspect($mockVisitor, $mockContent));
    }

    /**
     * @test
     * @group  issue_10
     * @since  0.10.0
     */
    public function inspectWithoutContentGivesRootToVisitor()
    {
        $root = vfsStream::setup();
        $mockVisitor = $this->getMock('vfsStreamVisitor');
        $mockVisitor->expects($this->once())
                    ->method('visitDirectory')
                    ->with($this->equalTo($root))
                    ->will($this->returnValue($mockVisitor));
        $this->assertSame($mockVisitor, vfsStream::inspect($mockVisitor));
    }

    /**
     * @test
     * @group  issue_10
     * @expectedException  InvalidArgumentException
     * @since  0.10.0
     */
    public function inspectWithoutContentAndWithoutRootThrowsInvalidArgumentException()
    {
        $mockVisitor = $this->getMock('vfsStreamVisitor');
        $mockVisitor->expects($this->never())
                    ->method('visit');
        $mockVisitor->expects($this->never())
                    ->method('visitDirectory');
        vfsStream::inspect($mockVisitor);
    }
}
?>