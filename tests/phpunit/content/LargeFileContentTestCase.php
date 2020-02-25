<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests\content;

use bovigo\vfs\content\LargeFileContent;
use PHPUnit\Framework\TestCase;
use const SEEK_END;
use const SEEK_SET;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function str_repeat;

/**
 * Test for bovigo\vfs\content\LargeFileContent.
 *
 * @since  1.3.0
 * @group  issue_79
 */
class LargeFileContentTestCase extends TestCase
{
    /**
     * instance to test
     *
     * @var LargeFileContent
     */
    private $largeFileContent;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $this->largeFileContent = new LargeFileContent(100);
    }

    /**
     * @test
     */
    public function hasSizeOriginallyGiven(): void
    {
        assertThat($this->largeFileContent->size(), equals(100));
    }

    /**
     * @test
     */
    public function contentIsFilledUpWithSpacesIfNoDataWritten(): void
    {
        assertThat($this->largeFileContent->content(), equals(str_repeat(' ', 100)));
    }

    /**
     * @test
     */
    public function readReturnsSpacesWhenNothingWrittenAtOffset(): void
    {
        assertThat($this->largeFileContent->read(0, 10), equals(str_repeat(' ', 10)));
    }

    /**
     * @test
     */
    public function readReturnsContentFilledWithSpaces(): void
    {
        $this->largeFileContent->write('foobarbaz', 0, 9);
        assertThat($this->largeFileContent->read(0, 10), equals('foobarbaz '));
    }

    /**
     * @test
     */
    public function writesDataAtStartWhenOffsetNotMoved(): void
    {
        $this->largeFileContent->write('foobarbaz', 0, 9);
        assertThat(
            $this->largeFileContent->content(),
            equals('foobarbaz' . str_repeat(' ', 91))
        );
    }

    /**
     * @test
     */
    public function writeDataAtStartDoesNotIncreaseSize(): void
    {
        $this->largeFileContent->write('foobarbaz', 0, 9);
        assertThat($this->largeFileContent->size(), equals(100));
    }

    /**
     * @test
     */
    public function writesDataAtOffsetWhenOffsetMoved(): void
    {
        $this->largeFileContent->write('foobarbaz', 50, 9);
        assertThat(
            $this->largeFileContent->content(),
            equals(str_repeat(' ', 50) . 'foobarbaz' . str_repeat(' ', 41))
        );
    }

    /**
     * @test
     */
    public function writeDataInBetweenDoesNotIncreaseSize(): void
    {
        $this->largeFileContent->write('foobarbaz', 50, 9);
        assertThat($this->largeFileContent->size(), equals(100));
    }

    /**
     * @test
     */
    public function writesDataOverEndWhenOffsetAndDataLengthLargerThanSize(): void
    {
        $this->largeFileContent->write('foobarbaz', 95, 9);
        assertThat(
            $this->largeFileContent->content(),
            equals(str_repeat(' ', 95) . 'foobarbaz')
        );
    }

    /**
     * @test
     */
    public function writeDataOverLastOffsetIncreasesSize(): void
    {
        $this->largeFileContent->write('foobarbaz', 95, 9);
        assertThat($this->largeFileContent->size(), equals(104));
    }

    /**
     * @test
     */
    public function writesDataAfterEndWhenOffsetAfterEnd(): void
    {
        $this->largeFileContent->write('foobarbaz', 100, 9);
        assertThat(
            $this->largeFileContent->content(),
            equals(str_repeat(' ', 100) . 'foobarbaz')
        );
    }

    /**
     * @test
     */
    public function writeDataAfterLastOffsetIncreasesSize(): void
    {
        $this->largeFileContent->write('foobarbaz', 100, 9);
        assertThat($this->largeFileContent->size(), equals(109));
    }

    /**
     * @test
     */
    public function truncateReducesSize(): void
    {
        assertTrue($this->largeFileContent->truncate(50));
        assertThat($this->largeFileContent->size(), equals(50));
    }

    /**
     * @test
     */
    public function truncateRemovesWrittenContentAfterOffset(): void
    {
        $this->largeFileContent->write('foobarbaz', 45, 9);
        $this->largeFileContent->truncate(50);
        assertThat(
            $this->largeFileContent->content(),
            equals(str_repeat(' ', 45) . 'fooba')
        );
    }

    /**
     * @test
     */
    public function createInstanceWithKilobytes(): void
    {
        assertThat(LargeFileContent::withKilobytes(100)->size(), equals(100 * 1024));
    }

    /**
     * @test
     */
    public function createInstanceWithMegabytes(): void
    {
        assertThat(LargeFileContent::withMegabytes(100)->size(), equals(100 * 1024 * 1024));
    }

    /**
     * @test
     */
    public function createInstanceWithGigabytes(): void
    {
        assertThat(LargeFileContent::withGigabytes(100)->size(), equals(100 * 1024 * 1024 * 1024));
    }
}
