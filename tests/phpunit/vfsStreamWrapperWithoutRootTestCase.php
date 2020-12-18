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
use bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertFalse;
use function dir;
use function fopen;
use function rename;
use function unlink;

/**
 * Test for bovigo\vfs\vfsStreamWrapper.
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
    public function canNotOpenDirectory(): void
    {
        assertFalse(@dir(vfsStream::url('foo')));
    }

    /**
     * @test
     */
    public function canNotUnlink(): void
    {
        assertFalse(@unlink(vfsStream::url('foo')));
    }

    /**
     * @test
     */
    public function canNotOpen(): void
    {
        assertFalse(@fopen(vfsStream::url('foo'), 'r'));
    }

    /**
     * @test
     */
    public function canNotRename(): void
    {
        assertFalse(@rename(vfsStream::url('foo'), vfsStream::url('bar')));
    }
}
