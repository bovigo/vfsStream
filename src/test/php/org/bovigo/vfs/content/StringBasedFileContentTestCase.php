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

use function bovigo\assert\assertThat;
use function bovigo\assert\assertEmptyString;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
/**
 * Test for org\bovigo\vfs\content\StringBasedFileContent.
 *
 * @since  1.3.0
 * @group  issue_79
 */
class StringBasedFileContentTestCase extends TestCase
{
    /**
     * instance to test
     *
     * @type  StringBasedFileContent
     */
    private $stringBasedFileContent;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->stringBasedFileContent = new StringBasedFileContent('foobarbaz');
    }

    /**
     * @test
     */
    public function hasContentOriginallySet()
    {
        assertThat($this->stringBasedFileContent->content(), equals('foobarbaz'));
    }

    /**
     * @test
     */
    public function hasNotReachedEofAfterCreation()
    {
        assertFalse($this->stringBasedFileContent->eof());
    }

    /**
     * @test
     */
    public function sizeEqualsLengthOfGivenString()
    {
        assertThat($this->stringBasedFileContent->size(), equals(9));
    }

    /**
     * @test
     */
    public function readReturnsSubstringWithRequestedLength()
    {
        assertThat($this->stringBasedFileContent->read(3), equals('foo'));
    }

    /**
     * @test
     */
    public function readMovesOffset()
    {
        assertThat($this->stringBasedFileContent->read(3), equals('foo'));
        assertThat($this->stringBasedFileContent->read(3), equals('bar'));
        assertThat($this->stringBasedFileContent->read(3), equals('baz'));
    }

    /**
     * @test
     */
    public function readMoreThanSizeReturnsWholeContent()
    {
        assertThat($this->stringBasedFileContent->read(10), equals('foobarbaz'));
    }

    /**
     * @test
     */
    public function readAfterEndReturnsEmptyString()
    {
        $this->stringBasedFileContent->read(9);
        assertEmptyString($this->stringBasedFileContent->read(3));
    }

    /**
     * @test
     */
    public function readDoesNotChangeSize()
    {
        $this->stringBasedFileContent->read(3);
        assertThat($this->stringBasedFileContent->size(), equals(9));
    }

    /**
     * @test
     */
    public function readLessThenSizeDoesNotReachEof()
    {
        $this->stringBasedFileContent->read(3);
        assertFalse($this->stringBasedFileContent->eof());
    }

    /**
     * @test
     */
    public function readSizeReachesEof()
    {
        $this->stringBasedFileContent->read(9);
        assertTrue($this->stringBasedFileContent->eof());
    }

    /**
     * @test
     */
    public function readMoreThanSizeReachesEof()
    {
        $this->stringBasedFileContent->read(10);
        assertTrue($this->stringBasedFileContent->eof());
    }

    /**
     * @test
     */
    public function seekWithInvalidOptionReturnsFalse()
    {
        assertFalse($this->stringBasedFileContent->seek(0, 55));
    }

    /**
     * @test
     */
    public function canSeekToGivenOffset()
    {
        assertTrue($this->stringBasedFileContent->seek(5, SEEK_SET));
        assertThat($this->stringBasedFileContent->read(10), equals('rbaz'));
    }

    /**
     * @test
     */
    public function canSeekFromCurrentOffset()
    {
        $this->stringBasedFileContent->seek(5, SEEK_SET);
        assertTrue($this->stringBasedFileContent->seek(2, SEEK_CUR));
        assertThat($this->stringBasedFileContent->read(10), equals('az'));
    }

    /**
     * @test
     */
    public function canSeekToEnd()
    {
        assertTrue($this->stringBasedFileContent->seek(0, SEEK_END));
        assertEmptyString($this->stringBasedFileContent->read(10));
    }

    /**
     * @test
     */
    public function writeOverwritesExistingContentWhenOffsetNotAtEof()
    {
        assertThat($this->stringBasedFileContent->write('bar'), equals(3));
        assertThat($this->stringBasedFileContent->content(), equals('barbarbaz'));
    }

    /**
     * @test
     */
    public function writeAppendsContentWhenOffsetAtEof()
    {
        $this->stringBasedFileContent->seek(0, SEEK_END);
        assertThat($this->stringBasedFileContent->write('bar'), equals(3));
        assertThat($this->stringBasedFileContent->content(), equals('foobarbazbar'));
    }

    /**
     * @test
     * @group  issue_33
     * @since  1.1.0
     */
    public function truncateRemovesSuperflouosContent()
    {
        assertTrue($this->stringBasedFileContent->truncate(6));
        assertThat($this->stringBasedFileContent->content(), equals('foobar'));
    }

    /**
     * @test
     * @group  issue_33
     * @since  1.1.0
     */
    public function truncateDecreasesSize()
    {
        assertTrue($this->stringBasedFileContent->truncate(6));
        assertThat($this->stringBasedFileContent->size(), equals(6));
    }

    /**
     * @test
     * @group  issue_33
     * @since  1.1.0
     */
    public function truncateToGreaterSizeAddsZeroBytes()
    {
        assertTrue($this->stringBasedFileContent->truncate(25));
        assertThat(
            $this->stringBasedFileContent->content(),
            equals("foobarbaz\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0")
        );
    }

    /**
     * @test
     * @group  issue_33
     * @since  1.1.0
     */
    public function truncateToGreaterSizeIncreasesSize()
    {
        assertTrue($this->stringBasedFileContent->truncate(25));
        assertThat($this->stringBasedFileContent->size(), equals(25));
    }
}
