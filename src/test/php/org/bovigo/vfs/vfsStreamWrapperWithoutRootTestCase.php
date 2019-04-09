<?php
declare(strict_types=1);
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
namespace org\bovigo\vfs;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertFalse;
/**
 * Test for org\bovigo\vfs\vfsStreamWrapper.
 */
class vfsStreamWrapperWithoutRootTestCase extends TestCase
{
    /**
     * set up test environment but without root
     */
    protected function setUp(): void
    {
        vfsStreamWrapper::register();
    }

    /**
     * @test
     */
    public function canNotOpenDirectory()
    {
        assertFalse(@dir(vfsStream::url('foo')));
    }

    /**
     * @test
     */
    public function canNotUnlink()
    {
        assertFalse(@unlink(vfsStream::url('foo')));
    }

    /**
     * @test
     */
    public function canNotOpen()
    {
        assertFalse(@fopen(vfsStream::url('foo'), 'r'));
    }

    /**
     * @test
     */
    public function canNotRename()
    {
        assertFalse(@rename(vfsStream::url('foo'), vfsStream::url('bar')));
    }
}
