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
 * Test for stream_cast, e.g. compress.zlib://vfs://root/test.nbt.
 *
 * @group  issue_125
 */
class StreamCastTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @type  \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->root = vfsStream::setup('root');
    }

    /**
     * @test
     */
    public function canUseCompressZlibInConjunctionWithVfsUrl()
    {
        file_put_contents('compress.zlib://vfs://root/test.nbt', 'barbaz');
        $this->assertEquals(
                'barbaz',
                file_get_contents('compress.zlib://vfs://root/test.nbt')
        );
    }

    /**
     * @test
     */
    public function canOpenTwoCompressZlibUrlsAtTheSameTime()
    {
        $file1 = fopen('compress.zlib://vfs://root/test.nbt', 'wb+');
        $file2 = fopen('compress.zlib://vfs://root/other.nbt', 'wb+');
        fwrite($file2, 'Yippie ki-yay');
        fwrite($file1, 'barbaz');
        fclose($file2);
        fclose($file1);
        $this->assertEquals(
                'barbaz',
                file_get_contents('compress.zlib://vfs://root/test.nbt')
        );
        $this->assertEquals(
                'Yippie ki-yay',
                file_get_contents('compress.zlib://vfs://root/other.nbt')
        );

    }
}
