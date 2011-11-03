<?php
/**
 * Test that using windows directory separator works correct.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
require_once 'org/bovigo/vfs/vfsStream.php';
require_once 'PHPUnit/Framework/TestCase.php';
/**
 * Test that using windows directory separator works correct.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 * @since       0.9.0
 * @group       issue_8
 */
class vfsStreamWrapperDirSeparatorTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * root diretory
     *
     * @var  vfsStreamDirectory
     */
    protected $root;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->root = vfsStream::setup();
    }

    /**
     * @test
     */
    public function fileCanBeAccessedUsingWinDirSeparator()
    {
        vfsStream::newFile('foo/bar/baz.txt')
                 ->at($this->root)
                 ->withContent('test');
        $this->assertEquals('test', file_get_contents('vfs://root/foo\bar\baz.txt'));
    }


    /**
     * @test
     */
    public function directoryCanBeCreatedUsingWinDirSeparator()
    {
        mkdir('vfs://root/dir\bar\foo', true, 0777);
        $this->assertTrue($this->root->hasChild('dir'));
        $this->assertTrue($this->root->getChild('dir')->hasChild('bar'));
        $this->assertTrue($this->root->getChild('dir/bar')->hasChild('foo'));
    }
}
?>