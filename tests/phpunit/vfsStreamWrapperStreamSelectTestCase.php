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
use PHPUnit\Framework\TestCase;

use function bovigo\assert\expect;
use function fopen;
use function stream_select;

use const E_WARNING;

/**
 * Test for bovigo\vfs\vfsStreamWrapper.
 *
 * @since  0.9.0
 * @group  issue_3
 */
class vfsStreamWrapperStreamSelectTestCase extends TestCase
{
    /**
     * @test
     * @requires PHP < 8
     */
    public function selectStreamDoesNotWorkPHP7(): void
    {
        $root = vfsStream::setup();
        $file = vfsStream::newFile('foo.txt')->at($root)->withContent('testContent');
        $read = [fopen(vfsStream::url('root/foo.txt'), 'rb')];
        $write = [];
        $except = [];
        expect(static function () use ($read, $write, $except): void {
            stream_select($read, $write, $except, 1);
        })->triggers(E_WARNING)
          ->withMessage('stream_select(): No stream arrays were passed');
    }

    /**
     * @test
     * @requires PHP >= 8
     */
    public function selectStreamDoesNotWork(): void
    {
        $root = vfsStream::setup();
        $file = vfsStream::newFile('foo.txt')->at($root)->withContent('testContent');
        $read = [fopen(vfsStream::url('root/foo.txt'), 'rb')];
        $write = [];
        $except = [];
        expect(static function () use ($read, $write, $except): void {
            stream_select($read, $write, $except, 1);
        })->triggers(E_WARNING)
            ->withMessage('stream_select(): Cannot represent a stream of type user-space as a select()able descriptor');
    }
}
