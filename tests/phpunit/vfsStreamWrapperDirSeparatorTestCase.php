<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function file_exists;
use function file_get_contents;
use function mkdir;

/**
 * Test that using windows directory separator works correct.
 *
 * @since  0.9.0
 * @group  issue_8
 */
class vfsStreamWrapperDirSeparatorTestCase extends \BC_PHPUnit_Framework_TestCase
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

    /**
     * @test
     */
    public function directoryExitsTestUsingTrailingWinDirSeparator()
    {
        $structure = array(
            'dir' => array(
                'bar' => array(
                )
            )
        );
        vfsStream::create($structure, $this->root);

        $this->assertTrue(file_exists(vfsStream::url('root/').'dir\\'));
    }
}
