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

use function bovigo\assert\assertFalse;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function clearstatcache;
use function file_put_contents;
use function fopen;
use function fstat;
use function uniqid;
use function unlink;

/**
 * Test for unlink() functionality.
 *
 * @group  unlink
 */
class UnlinkTestCase extends TestCase
{
    /**
     * @test
     * @group  issue_51
     */
    public function canUnlinkNonWritableFileFromWritableDirectory(): void
    {
        $structure = ['test_directory' => ['test.file' => '']];
        $root = vfsStream::setup('root', null, $structure);
        $root->getChild('test_directory')->chmod(0777);
        $root->getChild('test_directory')->getChild('test.file')->chmod(0444);
        assertTrue(@unlink(vfsStream::url('root/test_directory/test.file')));
    }

    /**
     * @test
     * @group  issue_51
     */
    public function canNotUnlinkWritableFileFromNonWritableDirectory(): void
    {
        $structure = ['test_directory' => ['test.file' => '']];
        $root = vfsStream::setup('root', null, $structure);
        $root->getChild('test_directory')->chmod(0444);
        $root->getChild('test_directory')->getChild('test.file')->chmod(0777);
        assertFalse(@unlink(vfsStream::url('root/test_directory/test.file')));
    }

    /**
     * @test
     * @since  1.4.0
     * @group  issue_68
     */
    public function unlinkNonExistingFileTriggersError(): void
    {
        vfsStream::setup();
        expect(static function (): void {
            assertFalse(unlink('vfs://root/foo.txt'));
        })
            ->triggers()
            ->withMessage('unlink(vfs://root/foo.txt): No such file or directory');
    }

    /**
     * @test
     * @group  issue_119
     */
    public function unlinkMaintainsInode(): void
    {
        $root = vfsStream::setup('root');
        $path = $root->url() . '/test';
        file_put_contents($path, uniqid());

        $handle = fopen($path, 'r');
        $before = fstat($handle);

        unlink($path);

        // Prove that we're not getting cached stats
        clearstatcache();

        $after = fstat($handle);

        assertThat($after, equals($before));
    }
}
