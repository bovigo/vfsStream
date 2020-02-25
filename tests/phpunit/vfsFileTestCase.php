<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests;

use bovigo\callmap\NewInstance;
use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamException;
use bovigo\vfs\vfsFile;
use bovigo\vfs\content\FileContent;
use bovigo\vfs\content\StringBasedFileContent;
use bovigo\vfs\internal\Type;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertEmptyString;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;

/**
 * Test for bovigo\vfs\vfsFile.
 */
class vfsFileTestCase extends TestCase
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
    public function invalidCharacterInNameThrowsException(): void
    {
        expect(static function (): void {
            new vfsFile('foo/bar');
        })
            ->throws(vfsStreamException::class);
    }

    /**
     * @test
     */
    public function isOfTypeFile(): void
    {
        assertThat($this->file->type(), equals(Type::FILE));
    }

    /**
     * @test
     */
    public function appliesForSelf(): void
    {
        assertTrue($this->file->appliesTo('foo'));
    }

    /**
     * @test
     */
    public function doesNotApplyForSubDirectories(): void
    {
        assertFalse($this->file->appliesTo('foo/bar'));
    }

    /**
     * @test
     */
    public function doesNotApplyForOtherNames(): void
    {
        assertFalse($this->file->appliesTo('bar'));
    }

    /**
     * @test
     */
    public function hasGivenName(): void
    {
        assertThat($this->file->name(), equals('foo'));
    }

    /**
     * @test
     */
    public function canBeRenamed(): void
    {
        $this->file->rename('bar');
        assertThat($this->file->name(), equals('bar'));
        assertFalse($this->file->appliesTo('foo'));
        assertFalse($this->file->appliesTo('foo/bar'));
        assertTrue($this->file->appliesTo('bar'));
    }

    /**
     * @test
     */
    public function renameToInvalidNameThrowsException(): void
    {
        expect(function (): void {
            $this->file->rename('foo/baz');
        })
            ->throws(vfsStreamException::class);
    }

    /**
     * @test
     */
    public function hasNoContentByDefault(): void
    {
        assertEmptyString($this->file->content());
    }

    /**
     * @test
     */
    public function contentCanBeChanged(): void
    {
        $this->file->setContent('bar');
        assertThat($this->file->content(), equals('bar'));
    }

    /**
     * @test
     */
    public function fileSizeIs0WhenEmpty(): void
    {
        assertThat($this->file->size(), equals(0));
    }

    /**
     * @test
     */
    public function fileSizeEqualsSizeOfContent(): void
    {
        $this->file->setContent('foobarbaz');
        assertThat($this->file->size(), equals(9));
    }

    /**
     * @test
     * @group  permissions
     */
    public function defaultPermissions(): void
    {
        assertThat($this->file->permissions(), equals(0666));
    }

    /**
     * @test
     * @group  permissions
     */
    public function permissionsCanBeChanged(): void
    {
        assertThat($this->file->chmod(0600)->permissions(), equals(0600));
    }

    /**
     * @test
     * @group  permissions
     */
    public function permissionsCanBeSetOnCreation(): void
    {
        assertThat(vfsStream::newFile('foo', 0644)->permissions(), equals(0644));
    }

    /**
     * @test
     * @group  permissions
     */
    public function currentUserIsDefaultOwner(): void
    {
        assertThat($this->file->user(), equals(vfsStream::getCurrentUser()));
        assertTrue($this->file->isOwnedByUser(vfsStream::getCurrentUser()));
    }

    /**
     * @test
     * @group  permissions
     */
    public function ownerCanBeChanged(): void
    {
        $this->file->chown(vfsStream::OWNER_USER_1);
        assertThat($this->file->user(), equals(vfsStream::OWNER_USER_1));
        assertTrue($this->file->isOwnedByUser(vfsStream::OWNER_USER_1));
    }

    /**
     * @test
     * @group  permissions
     */
    public function currentGroupIsDefaultGroup(): void
    {
        assertThat($this->file->group(), equals(vfsStream::getCurrentGroup()));
        assertTrue($this->file->isOwnedByGroup(vfsStream::getCurrentGroup()));
    }

    /**
     * @test
     * @group  permissions
     */
    public function groupCanBeChanged(): void
    {
        $this->file->chgrp(vfsStream::GROUP_USER_1);
        assertThat($this->file->group(), equals(vfsStream::GROUP_USER_1));
        assertTrue($this->file->isOwnedByGroup(vfsStream::GROUP_USER_1));
    }

    /**
     * @test
     * @group  issue_33
     * @since  1.1.0
     */
    // public function truncateRemovesSuperflouosContent(): void
    // {
    //     $this->file->write('lorem ipsum');
    //     assertTrue($this->file->truncate(5));
    //     assertThat($this->file->content(), equals('lorem'));
    // }

    /**
     * @test
     * @group  issue_33
     * @since  1.1.0
     */
    // public function truncateToGreaterSizeAddsZeroBytes(): void
    // {
    //     $this->file->write('lorem ipsum');
    //     assertTrue($this->file->truncate(25));
    //     assertThat(
    //         $this->file->content(),
    //         equals("lorem ipsum\0\0\0\0\0\0\0\0\0\0\0\0\0\0")
    //     );
    // }

    /**
     * @test
     * @group  issue_791
     * @since  1.3.0
     */
    public function withContentAcceptsAnyFileContentInstance(): void
    {
        $fileContent = NewInstance::of(FileContent::class)->returns(['content' => 'foobarbaz']);
        assertThat(
            $this->file->withContent($fileContent)->content(),
            equals('foobarbaz')
        );
    }

    /**
     * @test
     * @group  issue_791
     * @since  1.3.0
     */
    public function withContentThrowsInvalidArgumentExceptionWhenContentIsNoStringAndNoFileContent(): void
    {
        expect(function (): void {
            $this->file->withContent(313);
        })
          ->throws(InvalidArgumentException::class);
    }
}
