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
namespace org\bovigo\vfs\content;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assert;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
/**
 * Test for org\bovigo\vfs\content\LargeFileContent.
 *
 * @since  1.3.0
 * @group  issue_79
 */
class LargeFileContentTestCase extends TestCase
{
    /**
     * instance to test
     *
     * @type  LargeFileContent
     */
    private $largeFileContent;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->largeFileContent = new LargeFileContent(100);
    }

    /**
     * @test
     */
    public function hasSizeOriginallyGiven()
    {
        assert($this->largeFileContent->size(), equals(100));
    }

    /**
     * @test
     */
    public function contentIsFilledUpWithSpacesIfNoDataWritten()
    {
        assert($this->largeFileContent->content(), equals(str_repeat(' ', 100)));
    }

    /**
     * @test
     */
    public function readReturnsSpacesWhenNothingWrittenAtOffset()
    {
        assert($this->largeFileContent->read(10), equals(str_repeat(' ', 10)));
    }

    /**
     * @test
     */
    public function readReturnsContentFilledWithSpaces()
    {
        $this->largeFileContent->write('foobarbaz');
        $this->largeFileContent->seek(0, SEEK_SET);
        assert($this->largeFileContent->read(10), equals('foobarbaz '));
    }

    /**
     * @test
     */
    public function writeReturnsAmounfOfWrittenBytes()
    {
        assert($this->largeFileContent->write('foobarbaz'), equals(9));
    }

    /**
     * @test
     */
    public function writesDataAtStartWhenOffsetNotMoved()
    {
        $this->largeFileContent->write('foobarbaz');
        assert(
            $this->largeFileContent->content(),
            equals('foobarbaz' . str_repeat(' ', 91))
        );
    }

    /**
     * @test
     */
    public function writeDataAtStartDoesNotIncreaseSize()
    {
        $this->largeFileContent->write('foobarbaz');
        assert($this->largeFileContent->size(), equals(100));
    }

    /**
     * @test
     */
    public function writesDataAtOffsetWhenOffsetMoved()
    {
        $this->largeFileContent->seek(50, SEEK_SET);
        $this->largeFileContent->write('foobarbaz');
        assert(
            $this->largeFileContent->content(),
            equals(str_repeat(' ', 50) . 'foobarbaz' . str_repeat(' ', 41))
        );
    }

    /**
     * @test
     */
    public function writeDataInBetweenDoesNotIncreaseSize()
    {
        $this->largeFileContent->seek(50, SEEK_SET);
        $this->largeFileContent->write('foobarbaz');
        assert($this->largeFileContent->size(), equals(100));
    }

    /**
     * @test
     */
    public function writesDataOverEndWhenOffsetAndDataLengthLargerThanSize()
    {
        $this->largeFileContent->seek(95, SEEK_SET);
        $this->largeFileContent->write('foobarbaz');
        assert(
            $this->largeFileContent->content(),
            equals(str_repeat(' ', 95) . 'foobarbaz')
        );
    }

    /**
     * @test
     */
    public function writeDataOverLastOffsetIncreasesSize()
    {
        $this->largeFileContent->seek(95, SEEK_SET);
        $this->largeFileContent->write('foobarbaz');
        assert($this->largeFileContent->size(), equals(104));
    }

    /**
     * @test
     */
    public function writesDataAfterEndWhenOffsetAfterEnd()
    {
        $this->largeFileContent->seek(0, SEEK_END);
        $this->largeFileContent->write('foobarbaz');
        assert(
            $this->largeFileContent->content(),
            equals(str_repeat(' ', 100) . 'foobarbaz')
        );
    }

    /**
     * @test
     */
    public function writeDataAfterLastOffsetIncreasesSize()
    {
        $this->largeFileContent->seek(0, SEEK_END);
        $this->largeFileContent->write('foobarbaz');
        assert($this->largeFileContent->size(), equals(109));
    }

    /**
     * @test
     */
    public function truncateReducesSize()
    {
        assertTrue($this->largeFileContent->truncate(50));
        assert($this->largeFileContent->size(), equals(50));
    }

    /**
     * @test
     */
    public function truncateRemovesWrittenContentAfterOffset()
    {
        $this->largeFileContent->seek(45, SEEK_SET);
        $this->largeFileContent->write('foobarbaz');
        $this->largeFileContent->truncate(50);
        assert(
            $this->largeFileContent->content(),
            equals(str_repeat(' ', 45) . 'fooba')
        );
    }

    /**
     * @test
     */
    public function createInstanceWithKilobytes()
    {
        assert(LargeFileContent::withKilobytes(100)->size(), equals(100 * 1024));
    }

    /**
     * @test
     */
    public function createInstanceWithMegabytes()
    {
        assert(LargeFileContent::withMegabytes(100)->size(), equals(100 * 1024 * 1024));
    }

    /**
     * @test
     */
    public function createInstanceWithGigabytes()
    {
        assert(LargeFileContent::withGigabytes(100)->size(), equals(100 * 1024 * 1024 * 1024));
    }
}
