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
use ZipArchive;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;

use const DIRECTORY_SEPARATOR;

/**
 * Test for bovigo\vfs\vfsStreamWrapper in conjunction with ext/zip.
 *
 * @group  zip
 */
class vfsStreamZipTestCase extends TestCase
{
    /**
     * @test
     * @requires extension zip
     */
    public function zipExtensionDoesNotSupportUserlandStreams(): void
    {
        vfsStream::setup();
        $zip = new ZipArchive();
        if (DIRECTORY_SEPARATOR === '\\') {
            assertThat(
                $zip->open(vfsStream::url('root/test.zip'), ZipArchive::CREATE),
                equals(ZipArchive::ER_READ)
            );
        } else {
            assertTrue($zip->open(vfsStream::url('root/test.zip'), ZipArchive::CREATE));
            assertTrue($zip->addFromString('testfile1.txt', "#1 This is a test string added as testfile1.txt.\n"));
            assertTrue($zip->addFromString('testfile2.txt', "#2 This is a test string added as testfile2.txt.\n"));
            $zip->setArchiveComment('a test');
            expect(static function () use ($zip): void {
                $zip->close();
            })
              ->triggers()
              ->withMessage('ZipArchive::close(): Failure to create temporary file: No such file or directory');
        }
    }
}
