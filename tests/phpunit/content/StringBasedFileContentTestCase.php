<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests\content;

use bovigo\vfs\content\StringBasedFileContent;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertEmptyString;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;

/**
 * Test for bovigo\vfs\content\StringBasedFileContent.
 *
 * @since  1.3.0
 * @group  issue_79
 */
class StringBasedFileContentTestCase extends TestCase
{
    /**
     * instance to test
     *
     * @var StringBasedFileContent
     */
    private $stringBasedFileContent;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $this->stringBasedFileContent = new StringBasedFileContent('foobarbaz');
    }

    /**
     * @test
     */
    public function hasContentOriginallySet(): void
    {
        assertThat($this->stringBasedFileContent->content(), equals('foobarbaz'));
    }

    /**
     * @test
     */
    public function sizeEqualsLengthOfGivenString(): void
    {
        assertThat($this->stringBasedFileContent->size(), equals(9));
    }

    /**
     * @test
     */
    public function readReturnsSubstringWithRequestedLength(): void
    {
        assertThat($this->stringBasedFileContent->read(0, 3), equals('foo'));
    }

    /**
     * @test
     */
    public function readFromOffset(): void
    {
        assertThat($this->stringBasedFileContent->read(0, 3), equals('foo'));
        assertThat($this->stringBasedFileContent->read(3, 3), equals('bar'));
        assertThat($this->stringBasedFileContent->read(6, 3), equals('baz'));
    }

    /**
     * @test
     */
    public function readMoreThanSizeReturnsWholeContent(): void
    {
        assertThat($this->stringBasedFileContent->read(0, 10), equals('foobarbaz'));
    }

    /**
     * @test
     */
    public function readAfterEndReturnsEmptyString(): void
    {
        assertEmptyString($this->stringBasedFileContent->read(9, 3));
    }

    /**
     * @test
     */
    public function readDoesNotChangeSize(): void
    {
        $this->stringBasedFileContent->read(0, 3);
        assertThat($this->stringBasedFileContent->size(), equals(9));
    }

    /**
     * @test
     */
    public function writeOverwritesExistingContentWhenOffsetNotAtEof(): void
    {
        $this->stringBasedFileContent->write('bar', 0, 3);
        assertThat($this->stringBasedFileContent->content(), equals('barbarbaz'));
    }

    /**
     * @test
     */
    public function writeAppendsContentWhenOffsetAtEof(): void
    {
        $this->stringBasedFileContent->write('bar', 9, 3);
        assertThat($this->stringBasedFileContent->content(), equals('foobarbazbar'));
    }

    /**
     * @test
     * @group  issue_33
     * @since  1.1.0
     */
    public function truncateRemovesSuperflouosContent(): void
    {
        assertTrue($this->stringBasedFileContent->truncate(6));
        assertThat($this->stringBasedFileContent->content(), equals('foobar'));
    }

    /**
     * @test
     * @group  issue_33
     * @since  1.1.0
     */
    public function truncateDecreasesSize(): void
    {
        assertTrue($this->stringBasedFileContent->truncate(6));
        assertThat($this->stringBasedFileContent->size(), equals(6));
    }

    /**
     * @test
     * @group  issue_33
     * @since  1.1.0
     */
    public function truncateToGreaterSizeAddsZeroBytes(): void
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
    public function truncateToGreaterSizeIncreasesSize(): void
    {
        assertTrue($this->stringBasedFileContent->truncate(25));
        assertThat($this->stringBasedFileContent->size(), equals(25));
    }
}
