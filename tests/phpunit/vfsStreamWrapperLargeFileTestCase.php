<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\content\LargeFileContent;
use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
use function fclose;
use function filesize;
use function fopen;
use function fread;
use function fseek;
use function fwrite;
use function str_repeat;

use const PHP_INT_MAX;
use const SEEK_SET;

/**
 * Test for large file mocks.
 *
 * @since       1.3.0
 * @group       issue_79
 */
class vfsStreamWrapperLargeFileTestCase extends TestCase
{
    /**
     * large file to test
     *
     * @var  vfsStreamFile
     */
    private $largeFile;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $root = vfsStream::setup();
        $this->largeFile = vfsStream::newFile('large.txt')
            ->withContent(LargeFileContent::withGigabytes(100))
            ->at($root);
    }

    /**
     * @test
     */
    public function hasLargeFileSize(): void
    {
        if (PHP_INT_MAX === 2147483647) {
            $this->markTestSkipped('Requires 64-bit version of PHP');
        }

        assertThat(filesize($this->largeFile->url()), equals(100 * 1024 * 1024 * 1024));
    }

    /**
     * @test
     */
    public function canReadFromLargeFile(): void
    {
        $fp = fopen($this->largeFile->url(), 'rb');
        $data = fread($fp, 15);
        fclose($fp);
        assertThat($data, equals(str_repeat(' ', 15)));
    }

    /**
     * @test
     */
    public function canWriteIntoLargeFile(): void
    {
        $fp = fopen($this->largeFile->url(), 'rb+');
        fseek($fp, 100 * 1024 * 1024, SEEK_SET);
        fwrite($fp, 'foobarbaz');
        fclose($fp);
        $this->largeFile->seek((100 * 1024 * 1024) - 3, SEEK_SET);
        assertThat($this->largeFile->read(15), equals('   foobarbaz   '));
    }
}
