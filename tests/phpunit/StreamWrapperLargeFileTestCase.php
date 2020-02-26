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
use bovigo\vfs\vfsFile;
use bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use const PHP_INT_MAX;
use const SEEK_SET;
use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
use function fclose;
use function filesize;
use function fopen;
use function fread;
use function fseek;
use function fwrite;
use function str_repeat;

/**
 * Test for large file mocks.
 *
 * @since       1.3.0
 * @group       issue_79
 */
class StreamWrapperLargeFileTestCase extends TestCase
{
    /**
     * large file to test
     *
     * @var  vfsFile
     */
    private $largeFile;
    /** @var  LargeFileContent */
    private $content;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $root = vfsStream::setup();
        $this->content = LargeFileContent::withGigabytes(100);
        $this->largeFile = vfsStream::newFile('large.txt')
            ->withContent($this->content)
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
        assertThat($this->content->read((100 * 1024 * 1024) - 3, 15), equals('   foobarbaz   '));
    }
}
