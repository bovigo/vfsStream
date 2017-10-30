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

use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Test for org\bovigo\vfs\vfsStreamWrapper in conjunction with ext/zip.
 *
 * @group  zip
 */
class vfsStreamZipTestCase extends TestCase
{
    /**
     * @test
     * @requires extension zip
     */
    public function zipExtensionDoesNotSupportUserlandStreams()
    {
        vfsStream::setup();
        $zip = new \ZipArchive();
        if (DIRECTORY_SEPARATOR == '\\') {
            assertThat(
                $zip->open(vfsStream::url('root/test.zip'), \ZipArchive::CREATE),
                equals(\ZipArchive::ER_READ)
            );
        } else {
            assertTrue($zip->open(vfsStream::url('root/test.zip'), \ZipArchive::CREATE));
            assertTrue($zip->addFromString("testfile1.txt", "#1 This is a test string added as testfile1.txt.\n"));
            assertTrue($zip->addFromString("testfile2.txt", "#2 This is a test string added as testfile2.txt.\n"));
            $zip->setArchiveComment('a test');
            expect(function() use ($zip) { $zip->close(); })
              ->triggers()
              ->withMessage('ZipArchive::close(): Failure to create temporary file: No such file or directory');
        }
    }
}
