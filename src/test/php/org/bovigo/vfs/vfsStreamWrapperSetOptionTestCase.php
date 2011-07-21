<?php
/**
 * Test for stream_set_option() implementation.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
require_once 'org/bovigo/vfs/vfsStream.php';
require_once 'PHPUnit/Framework.php';
/**
 * Test for stream_set_option() implementation.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 * @since       0.10.0
 * @see         https://github.com/mikey179/vfsStream/issues/15
 * @group       issue_15
 */
class vfsStreamWrapperSetOptionTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * root directory
     *
     * @var  vfsStreamContainer
     */
    protected $root;
    
    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->root = vfsStream::setup();
        vfsStream::newFile('foo.txt')->at($this->root);
    }

    /**
     * @test
     */
    public function setBlockingDoesNotWork()
    {
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $this->assertFalse(stream_set_blocking($fp, 1));
        fclose($fp);
    }

    /**
     * @test
     */
    public function removeBlockingDoesNotWork()
    {
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $this->assertFalse(stream_set_blocking($fp, 0));
        fclose($fp);
    }

    /**
     * @test
     */
    public function setTimeoutDoesNotWork()
    {
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $this->assertFalse(stream_set_timeout($fp, 1));
        fclose($fp);
    }

    /**
     * @test
     */
    public function setWriteBufferDoesNotWork()
    {
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $this->assertEquals(-1, stream_set_write_buffer($fp, 512));
        fclose($fp);
    }
}
?>