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
use bovigo\callmap\NewInstance;
use org\bovigo\vfs\content\FileContent;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertEmptyString;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Test for org\bovigo\vfs\vfsStreamFile.
 */
class vfsStreamFileTestCase extends TestCase
{
    /**
     * instance to test
     *
     * @var  vfsStreamFile
     */
    protected $file;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $this->file = vfsStream::newFile('foo');
    }

    /**
     * @test
     */
    public function invalidCharacterInNameThrowsException()
    {
        expect(function() { new vfsStreamFile('foo/bar'); })
            ->throws(vfsStreamException::class);
    }

    /**
     * @test
     */
    public function isOfTypeFile()
    {
        assertThat($this->file->getType(), equals(vfsStreamContent::TYPE_FILE));
    }

    /**
     * @test
     */
    public function appliesForSelf()
    {
        assertTrue($this->file->appliesTo('foo'));
    }

    /**
     * @test
     */
    public function doesNotApplyForSubDirectories()
    {
        assertFalse($this->file->appliesTo('foo/bar'));
    }

    /**
     * @test
     */
    public function doesNotApplyForOtherNames()
    {
        assertFalse($this->file->appliesTo('bar'));
    }

    /**
     * @test
     */
    public function hasGivenName()
    {
        assertThat($this->file->getName(), equals('foo'));
    }

    /**
     * @test
     */
    public function canBeRenamed()
    {
        $this->file->rename('bar');
        assertThat($this->file->getName(), equals('bar'));
        assertFalse($this->file->appliesTo('foo'));
        assertFalse($this->file->appliesTo('foo/bar'));
        assertTrue($this->file->appliesTo('bar'));
    }

    /**
     * @test
     */
    public function renameToInvalidNameThrowsException()
    {
        expect(function() { $this->file->rename('foo/baz'); })
            ->throws(vfsStreamException::class);
    }

    /**
     * @test
     */
    public function hasNoContentByDefault()
    {
      assertEmptyString($this->file->getContent());
    }

    /**
     * @test
     */
    public function contentCanBeChanged()
    {
        $this->file->setContent('bar');
        assertThat($this->file->getContent(), equals('bar'));
    }

    /**
     * @test
     */
    public function isAtEofWhenEmpty()
    {
        assertTrue($this->file->eof());
    }

    /**
     * @test
     */
    public function fileSizeIs0WhenEmpty()
    {
        assertThat($this->file->size(), equals(0));
    }

    /**
     * @test
     */
    public function readFromEmptyFileReturnsEmptyString()
    {
        assertEmptyString($this->file->read(5));
    }

    /**
     * @test
     */
    public function readFromEmptyFileMovesPointer()
    {
        $this->file->read(5);
        assertThat($this->file->getBytesRead(), equals(5));
    }

    /**
     * @test
     */
    public function reportsAmountOfBytesReadEvenWhenEmpty()
    {
        $this->file->read(5);
        assertThat($this->file->getBytesRead(), equals(5));
    }

    /**
     * @test
     */
    public function isNotAtEofWhenNotAllContentRead()
    {
        $this->file->setContent('foobarbaz');
        assertFalse($this->file->eof());
    }

    /**
     * @test
     */
    public function fileSizeEqualsSizeOfContent()
    {
        $this->file->setContent('foobarbaz');
        assertThat($this->file->size(), equals(9));
    }

    /**
     * @test
     */
    public function readDoesNotChangeFileSize()
    {
        $this->file->setContent('foobarbaz');
        $this->file->read(3);
        assertThat($this->file->size(), equals(9));
    }

    /**
     * @test
     */
    public function partialReads()
    {
        $this->file->setContent('foobarbaz');
        assertThat($this->file->read(3), equals('foo'));
        assertThat($this->file->getBytesRead(), equals(3));
        assertFalse($this->file->eof());

        assertThat($this->file->read(3), equals('bar'));
        assertThat($this->file->getBytesRead(), equals(6));
        assertFalse($this->file->eof());

        assertThat($this->file->read(3), equals('baz'));
        assertThat($this->file->getBytesRead(), equals(9));
        assertTrue($this->file->eof());
    }

    /**
     * @test
     */
    public function readAfterEofReturnsEmptyString()
    {
        $this->file->setContent('foobarbaz');
        $this->file->read(9);
        assertEmptyString($this->file->read(3));
    }

    /**
     * @test
     */
    public function seekWithInvalidSeekCommandReturnsFalse()
    {
        assertFalse($this->file->seek(0, 55));
    }

    public function seeks(): array
    {
      return [
          [0, SEEK_SET, 0, 'foobarbaz'],
          [5, SEEK_SET, 5, 'rbaz'],
          [0, SEEK_END, 0, ''],
          [2, SEEK_END, 2, ''],
      ];
    }

    /**
     * @test
     * @dataProvider  seeks
     */
    public function seekEmptyFile(int $offset, $whence, int $expected)
    {
        assertTrue($this->file->seek($offset, $whence));
        assertThat($this->file->getBytesRead(), equals($expected));
    }

    /**
     * @test
     */
    public function seekEmptyFileWithSEEK_CUR()
    {
        $this->file->seek(5, SEEK_SET);
        assertTrue($this->file->seek(0, SEEK_CUR));
        assertThat($this->file->getBytesRead(), equals(5));
        assertTrue($this->file->seek(2, SEEK_CUR));
        assertThat($this->file->getBytesRead(), equals(7));
    }

    /**
     * @test
     * @since 1.6.5
     */
    public function seekEmptyFileBeforeBeginningDoesNotChangeOffset()
    {
        assertFalse($this->file->seek(-5, SEEK_SET), 'Seek before beginning of file');
        assertThat($this->file->getBytesRead(), equals(0));
    }

    /**
     * @test
     * @dataProvider  seeks
     */
    public function seekRead(int $offset, $whence, int $expected, string $remaining)
    {
        $this->file->setContent('foobarbaz');
        if (SEEK_END === $whence) {
          $expected += 9;
        }

        assertTrue($this->file->seek($offset, $whence));
        assertThat($this->file->readUntilEnd(), equals($remaining));
        assertThat($this->file->getBytesRead(), equals($expected));
    }

    /**
     * @test
     */
    public function seekFileWithSEEK_CUR()
    {
        $this->file->setContent('foobarbaz');
        $this->file->seek(5, SEEK_SET);
        assertTrue($this->file->seek(0, SEEK_CUR));
        assertThat($this->file->readUntilEnd(), equals('rbaz'));
        assertThat($this->file->getBytesRead(), equals(5));
        assertTrue($this->file->seek(2, SEEK_CUR));
        assertThat($this->file->readUntilEnd(), equals('az'));
        assertThat($this->file->getBytesRead(), equals(7));
    }

    /**
     * @test
     * @since 1.6.5
     */
    public function seekFileBeforeBeginningDoesNotChangeOffset()
    {
        $this->file->setContent('foobarbaz');
        assertFalse($this->file->seek(-5, SEEK_SET), 'Seek before beginning of file');
        assertThat($this->file->getBytesRead(), equals(0));
    }

    /**
     * test writing data into the file
     *
     * @test
     */
    public function writeReturnsAmountsOfBytesWritten()
    {
        assertThat($this->file->write('foo'), equals(3));
    }

    /**
     * @test
     */
    public function writeEmptyFile()
    {
        $this->file->write('foo');
        $this->file->write('bar');
        assertThat($this->file->getContent(), equals('foobar'));
    }

    /**
     * @test
     */
    public function write()
    {
        $this->file->setContent('foobarbaz');
        $this->file->seek(3, SEEK_SET);
        $this->file->write('foo');
        assertThat($this->file->getContent(), equals('foofoobaz'));
    }

    /**
     * @test
     * @group  permissions
     */
    public function defaultPermissions()
    {
        assertThat($this->file->getPermissions(), equals(0666));
    }


    /**
     * @test
     * @group  permissions
     */
    public function permissionsCanBeChanged()
    {
        assertThat($this->file->chmod(0600)->getPermissions(), equals(0600));
    }

    /**
     * @test
     * @group  permissions
     */
    public function permissionsCanBeSetOnCreation()
    {
        assertThat(vfsStream::newFile('foo', 0644)->getPermissions(), equals(0644));
    }

    /**
     * @test
     * @group  permissions
     */
    public function currentUserIsDefaultOwner()
    {
        assertThat($this->file->getUser(), equals(vfsStream::getCurrentUser()));
        assertTrue($this->file->isOwnedByUser(vfsStream::getCurrentUser()));
    }

    /**
     * @test
     * @group  permissions
     */
    public function ownerCanBeChanged()
    {
        $this->file->chown(vfsStream::OWNER_USER_1);
        assertThat($this->file->getUser(), equals(vfsStream::OWNER_USER_1));
        assertTrue($this->file->isOwnedByUser(vfsStream::OWNER_USER_1));
    }

    /**
     * @test
     * @group  permissions
     */
    public function currentGroupIsDefaultGroup()
    {
        assertThat($this->file->getGroup(), equals(vfsStream::getCurrentGroup()));
        assertTrue($this->file->isOwnedByGroup(vfsStream::getCurrentGroup()));
    }

    /**
     * @test
     * @group  permissions
     */
    public function groupCanBeChanged()
    {
        $this->file->chgrp(vfsStream::GROUP_USER_1);
        assertThat($this->file->getGroup(), equals(vfsStream::GROUP_USER_1));
        assertTrue($this->file->isOwnedByGroup(vfsStream::GROUP_USER_1));
    }

    /**
     * @test
     * @group  issue_33
     * @since  1.1.0
     */
    public function truncateRemovesSuperflouosContent()
    {
        $this->file->write("lorem ipsum");
        assertTrue($this->file->truncate(5));
        assertThat($this->file->getContent(), equals('lorem'));
    }

    /**
     * @test
     * @group  issue_33
     * @since  1.1.0
     */
    public function truncateToGreaterSizeAddsZeroBytes()
    {
        $this->file->write("lorem ipsum");
        assertTrue($this->file->truncate(25));
        assertThat(
            $this->file->getContent(),
            equals("lorem ipsum\0\0\0\0\0\0\0\0\0\0\0\0\0\0")
        );
    }

    /**
     * @test
     * @group  issue_79
     * @since  1.3.0
     */
    public function withContentAcceptsAnyFileContentInstance()
    {
        $fileContent = NewInstance::of(FileContent::class)->returns([
            'content' => 'foobarbaz'
        ]);
        assertThat(
                $this->file->withContent($fileContent)->getContent(),
                equals('foobarbaz')
        );
    }

    /**
     * @test
     * @group  issue_79
     * @since  1.3.0
     */
    public function withContentThrowsInvalidArgumentExceptionWhenContentIsNoStringAndNoFileContent()
    {
        expect(function() { $this->file->withContent(313); })
          ->throws(\InvalidArgumentException::class);
    }
}
