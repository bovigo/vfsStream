<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamContainer;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertFalse;
use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
use function fclose;
use function fopen;
use function stream_set_blocking;
use function stream_set_timeout;
use function stream_set_write_buffer;

/**
 * Test for stream_set_option() implementation.
 *
 * @see    https://github.com/mikey179/vfsStream/issues/15
 *
 * @since  0.10.0
 * @group  issue_15
 */
class vfsStreamWrapperSetOptionTestCase extends TestCase
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
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
        vfsStream::newFile('foo.txt')->at($this->root);
    }

    /**
     * @test
     */
    public function setBlockingDoesNotWork(): void
    {
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        assertFalse(stream_set_blocking($fp, true));
        fclose($fp);
    }

    /**
     * @test
     */
    public function removeBlockingDoesNotWork(): void
    {
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        assertFalse(stream_set_blocking($fp, false));
        fclose($fp);
    }

    /**
     * @test
     */
    public function setTimeoutDoesNotWork(): void
    {
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        assertFalse(stream_set_timeout($fp, 1));
        fclose($fp);
    }

    /**
     * @test
     */
    public function setWriteBufferDoesNotWork(): void
    {
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        assertThat(stream_set_write_buffer($fp, 512), equals(-1));
        fclose($fp);
    }
}
