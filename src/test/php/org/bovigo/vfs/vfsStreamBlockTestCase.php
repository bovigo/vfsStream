<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
namespace org\bovigo\vfs;
/**
 * Test for org\bovigo\vfs\vfsStreamBlock.
 */
class vfsStreamBlockTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * The block device being tested.
     *
     * @var vfsStreamBlock $block
     */
    protected $block;

    public function setUp()
    {
        $this->block = new vfsStreamBlock('foo');
    }

    /**
     * test default values and methods
     *
     * @test
     */
    public function defaultValues()
    {
        $this->assertEquals(vfsStreamContent::TYPE_BLOCK, $this->block->getType());
        $this->assertEquals('foo', $this->block->getName());
    }

    /**
     * tests how external functions see this object
     *
     * @test
     */
    public function external()
    {
        $root = vfsStream::setup('root');
        $root->addChild(vfsStream::newBlock('foo'));
        $this->assertEquals('block', filetype(vfsStream::url('root/foo')));
    }
}
