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

use function bovigo\assert\expect;
/**
 * Test for org\bovigo\vfs\vfsStreamWrapper.
 *
 * @since  0.9.0
 * @group  issue_3
 */
class vfsStreamWrapperSelectStreamTestCase extends TestCase
{
    /**
     * @test
     */
    public function selectStreamDoesNotWork()
    {
        $root = vfsStream::setup();
        $file = vfsStream::newFile('foo.txt')->at($root)->withContent('testContent');
        $read   = [fopen(vfsStream::url('root/foo.txt'), 'rb')];
        $write  = [];
        $except = [];
        expect(function() use ($read, $write, $except) {
            stream_select($read, $write, $except, 1);
        })->triggers(E_WARNING)
          ->withMessage('stream_select(): No stream arrays were passed');
    }
}
