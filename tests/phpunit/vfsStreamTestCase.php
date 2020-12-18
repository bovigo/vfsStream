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
use bovigo\vfs\content\LargeFileContent;
use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamContent;
use bovigo\vfs\vfsStreamDirectory;
use bovigo\vfs\vfsStreamWrapper;
use bovigo\vfs\visitor\vfsStreamVisitor;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertFalse;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
use function bovigo\callmap\verify;
use function dirname;
use function file_exists;
use function fileperms;
use function realpath;

use const DIRECTORY_SEPARATOR;

/**
 * Test for bovigo\vfs\vfsStream.
 */
class vfsStreamTestCase extends TestCase
{
    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        vfsStreamWrapper::register();
    }

    /**
     * @return string[][]
     */
    public function pathes(): array
    {
        return [
            ['foo', 'vfs://foo'],
            ['foo/bar.baz', 'vfs://foo/bar.baz'],
            ['foo\bar.baz', 'vfs://foo/bar.baz'],
        ];
    }

    /**
     * @test
     * @dataProvider pathes
     */
    public function pathToUrlConversion(string $path, string $url): void
    {
        assertThat(vfsStream::url($path), equals($url));
    }

    /**
     * @return string[][]
     */
    public function urls(): array
    {
        return [
            ['vfs://foo', 'foo'],
            ['vfs://foo/bar.baz', 'foo/bar.baz'],
            ['vfs://foo\bar.baz', 'foo/bar.baz'],
            ['vfs://foo\\bar', 'foo/bar'],
            ['vfs://foo/bar ', 'foo/bar'],
            ['vfs://foo/bar/', 'foo/bar'],
            ['vfs://foo/bar/ ', 'foo/bar'],
            ['vfs://foo//bar', 'foo/bar'],
        ];
    }

    /**
     * @test
     * @dataProvider urls
     */
    public function urlToPathConversion(string $url, string $path): void
    {
        assertThat(vfsStream::path($url), equals($path));
    }

    /**
     * @return mixed[][]
     */
    public function createDirectories(): array
    {
        return [
            [vfsStream::newDirectory('foo/bar/baz'), 0777],
            [vfsStream::newDirectory('/foo/bar/baz', 0755), 0755],
        ];
    }

    /**
     * @test
     * @dataProvider createDirectories
     */
    public function newDirectoryCreatesStructureWhenNameContainsSlashes(
        vfsStreamDirectory $root,
        int $permissions
    ): void {
        assertThat($root->getPermissions(), equals($permissions));

        assertTrue($root->hasChild('bar'));
        assertTrue($root->hasChild('bar/baz'));
        assertFalse($root->hasChild('baz'));

        $bar = $root->getChild('bar');
        assertThat($bar->getPermissions(), equals($permissions));
        assertTrue($bar->hasChild('baz'));
        $baz1 = $bar->getChild('baz');

        assertThat($baz1->getPermissions(), equals($permissions));
        $baz2 = $root->getChild('bar/baz');
        assertThat($baz1, isSameAs($baz2));
    }

    /**
     * @test
     * @group  setup
     * @since  0.7.0
     */
    public function setupRegistersStreamWrapper(): void
    {
        $root = vfsStream::setup();
        assertThat(vfsStreamWrapper::getRoot(), isSameAs($root));
    }

    /**
     * @test
     * @group  setup
     * @since  0.7.0
     */
    public function setupCreatesRootDirectoryWithDefaultName(): void
    {
        $root = vfsStream::setup();
        assertThat($root->getName(), equals('root'));
    }

    /**
     * @test
     * @group  setup
     * @since  0.7.0
     */
    public function setupCreatesRootDirectoryWithDefaultPermissions(): void
    {
        $root = vfsStream::setup();
        assertThat($root->getPermissions(), equals(0777));
    }

    /**
     * @test
     * @group  setup
     * @since  0.7.0
     */
    public function setupCreatesRootDirectoryWithGivenNameAn(): void
    {
        $root = vfsStream::setup('foo');
        assertThat($root->getName(), equals('foo'));
    }

    /**
     * @test
     * @group  setup
     * @since  0.7.0
     */
    public function setupCreatesRootDirectoryWithPermissions(): void
    {
        $root = vfsStream::setup('foo', 0444);
        assertThat($root->getPermissions(), equals(0444));
    }

    /**
     * @test
     * @group  issue_14
     * @group  issue_20
     * @since  0.10.0
     */
    public function setupWithEmptyStructureIsEqualToSetup(): void
    {
        $root = vfsStream::setup('example', 0755, []);
        assertFalse($root->hasChildren());
    }

    /**
     * @test
     * @group  issue_14
     * @group  issue_20
     * @since  0.10.0
     */
    public function setupArraysAreTurnedIntoSubdirectories(): void
    {
        $root = vfsStream::setup('root', null, ['test' => []]);
        assertTrue($root->hasChildren());
        assertTrue($root->hasChild('test'));
        assertThat(
            $root->getChild('test'),
            isInstanceOf(vfsStreamDirectory::class)
        );
        assertFalse($root->getChild('test')->hasChildren());
    }

    /**
     * @test
     * @group  issue_14
     * @group  issue_20
     * @since  0.10.0
     */
    public function setupStringsAreTurnedIntoFilesWithContent(): void
    {
        $root = vfsStream::setup('root', null, ['test.txt' => 'some content']);
        assertTrue($root->hasChildren());
        assertTrue($root->hasChild('test.txt'));
        assertThat($root->getChild('test.txt')->getContent(), equals('some content'));
    }

    /**
     * @test
     * @group  issue_14
     * @group  issue_20
     * @since  0.10.0
     */
    public function setupWorksRecursively(): void
    {
        $root = vfsStream::setup(
            'root',
            null,
            [
                'test' => [
                    'foo' => ['test.txt' => 'hello'],
                    'baz.txt' => 'world',
                ],
            ]
        );
        assertTrue($root->hasChildren());
        assertTrue($root->hasChild('test'));
        $test = $root->getChild('test');
        assertTrue($test->hasChildren());
        assertTrue($test->hasChild('baz.txt'));
        assertThat($test->getChild('baz.txt')->getContent(), equals('world'));

        assertTrue($test->hasChild('foo'));
        $foo = $test->getChild('foo');
        assertTrue($foo->hasChildren());
        assertTrue($foo->hasChild('test.txt'));
        assertThat($foo->getChild('test.txt')->getContent(), equals('hello'));
    }

    /**
     * @test
     * @group  issue_17
     * @group  issue_20
     */
    public function setupCastsNumericDirectoriesToStrings(): void
    {
        $root = vfsStream::setup(
            'root',
            null,
            [2011 => ['test.txt' => 'some content']]
        );
        assertTrue($root->hasChild('2011'));

        $directory = $root->getChild('2011');
        assertThat($directory->getChild('test.txt')->getContent(), equals('some content'));

        assertTrue(file_exists('vfs://root/2011/test.txt'));
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createArraysAreTurnedIntoSubdirectories(): void
    {
        $baseDir = vfsStream::create(['test' => []], vfsStream::newDirectory('baseDir'));
        assertTrue($baseDir->hasChildren());
        assertTrue($baseDir->hasChild('test'));
        assertThat($baseDir->getChild('test'), isInstanceOf(vfsStreamDirectory::class));
        assertFalse($baseDir->getChild('test')->hasChildren());
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createArraysAreTurnedIntoSubdirectoriesOfRoot(): void
    {
        $root = vfsStream::setup();
        assertThat(vfsStream::create(['test' => []]), isSameAs($root));
        assertTrue($root->hasChildren());
        assertTrue($root->hasChild('test'));
        assertThat(
            $root->getChild('test'),
            isInstanceOf(vfsStreamDirectory::class)
        );
        assertFalse($root->getChild('test')->hasChildren());
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createThrowsExceptionIfNoBaseDirGivenAndNoRootSet(): void
    {
        expect(static function (): void {
            vfsStream::create(['test' => []]);
        })
          ->throws(InvalidArgumentException::class);
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createWorksRecursively(): void
    {
        $baseDir = vfsStream::create(
            [
                'test' => [
                    'foo' => ['test.txt' => 'hello'],
                    'baz.txt' => 'world',
                ],
            ],
            vfsStream::newDirectory('baseDir')
        );
        assertTrue($baseDir->hasChildren());
        assertTrue($baseDir->hasChild('test'));
        $test = $baseDir->getChild('test');

        assertTrue($test->hasChildren());
        assertTrue($test->hasChild('baz.txt'));
        assertThat($test->getChild('baz.txt')->getContent(), equals('world'));

        assertTrue($test->hasChild('foo'));
        $foo = $test->getChild('foo');

        assertTrue($foo->hasChildren());
        assertTrue($foo->hasChild('test.txt'));
        assertThat($foo->getChild('test.txt')->getContent(), equals('hello'));
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createWorksRecursivelyWithRoot(): void
    {
        $root = vfsStream::setup();
        assertThat(
            vfsStream::create([
                'test' => [
                    'foo' => ['test.txt' => 'hello'],
                    'baz.txt' => 'world',
                ],
            ]),
            isSameAs($root)
        );
        assertTrue($root->hasChildren());
        assertTrue($root->hasChild('test'));
        $test = $root->getChild('test');
        assertTrue($test->hasChildren());
        assertTrue($test->hasChild('baz.txt'));
        assertThat($test->getChild('baz.txt')->getContent(), equals('world'));

        assertTrue($test->hasChild('foo'));
        $foo = $test->getChild('foo');
        assertTrue($foo->hasChildren());
        assertTrue($foo->hasChild('test.txt'));
        assertThat($foo->getChild('test.txt')->getContent(), equals('hello'));
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.10.0
     */
    public function createStringsAreTurnedIntoFilesWithContent(): void
    {
        $baseDir = vfsStream::create(
            ['test.txt' => 'some content'],
            vfsStream::newDirectory('baseDir')
        );
        assertTrue($baseDir->hasChildren());
        assertTrue($baseDir->hasChild('test.txt'));
        assertThat($baseDir->getChild('test.txt')->getContent(), equals('some content'));
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createStringsAreTurnedIntoFilesWithContentWithRoot(): void
    {
        $root = vfsStream::setup();
        vfsStream::create(['test.txt' => 'some content']);
        assertTrue($root->hasChildren());
        assertTrue($root->hasChild('test.txt'));
        assertThat($root->getChild('test.txt')->getContent(), equals('some content'));
    }

    /**
     * @test
     * @group issue_20
     * @since 0.11.0
     */
    public function createCastsNumericDirectoriesToStrings(): void
    {
        $baseDir = vfsStream::create(
            [2011 => ['test.txt' => 'some content']],
            vfsStream::newDirectory('baseDir')
        );
        assertTrue($baseDir->hasChild('2011'));

        $directory = $baseDir->getChild('2011');
        assertThat($directory->getChild('test.txt')->getContent(), equals('some content'));
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createCastsNumericDirectoriesToStringsWithRoot(): void
    {
        $root = vfsStream::setup();
        vfsStream::create([2011 => ['test.txt' => 'some content']]);
        assertTrue($root->hasChild('2011'));

        $directory = $root->getChild('2011');
        assertThat($directory->getChild('test.txt')->getContent(), equals('some content'));
    }

    /**
     * @test
     * @group  issue_10
     * @since  0.10.0
     */
    public function inspectReturnsGivenVisitor(): void
    {
        $content = NewInstance::of(vfsStreamContent::class);
        $visitor = NewInstance::of(vfsStreamVisitor::class);
        assertThat(vfsStream::inspect($visitor, $content), isSameAs($visitor));
    }

    /**
     * @test
     * @group  issue_10
     * @since  0.10.0
     */
    public function inspectWithContentGivesContentToVisitor(): void
    {
        $content = NewInstance::of(vfsStreamContent::class);
        $visitor = NewInstance::of(vfsStreamVisitor::class);
        vfsStream::inspect($visitor, $content);
        verify($visitor, 'visit')->received($content);
    }

    /**
     * @test
     * @group  issue_10
     */
    public function inspectWithoutContentGivesRootToVisitor(): void
    {
        $root = vfsStream::setup();
        $visitor = NewInstance::of(vfsStreamVisitor::class);
        vfsStream::inspect($visitor);
        verify($visitor, 'visitDirectory')->received($root);
    }

    /**
     * @test
     * @group  issue_10
     * @since  0.10.0
     */
    public function inspectWithoutContentAndWithoutRootThrowsInvalidArgumentException(): void
    {
        $visitor = NewInstance::of(vfsStreamVisitor::class);
        expect(static function () use ($visitor): void {
            vfsStream::inspect($visitor);
        })
          ->throws(InvalidArgumentException::class);
        verify($visitor, 'visit')->wasNeverCalled();
        verify($visitor, 'visitDirectory')->wasNeverCalled();
    }

    private function fileSystemCopyDir(): string
    {
        return realpath(dirname(__FILE__) . '/../resources/filesystemcopy');
    }

    /**
     * @test
     * @group  issue_4
     * @since  0.11.0
     */
    public function copyFromFileSystemThrowsExceptionIfNoBaseDirGivenAndNoRootSet(): void
    {
        expect(function (): void {
            vfsStream::copyFromFileSystem($this->fileSystemCopyDir());
        })->throws(InvalidArgumentException::class);
    }

    /**
     * @test
     * @group  issue_4
     * @since  0.11.0
     */
    public function copyFromEmptyFolder(): void
    {
        $baseDir = vfsStream::copyFromFileSystem(
            $this->fileSystemCopyDir() . '/emptyFolder',
            vfsStream::newDirectory('test')
        );
        $baseDir->removeChild('.gitignore');
        assertFalse($baseDir->hasChildren());
    }

    /**
     * @test
     * @group  issue_4
     * @since  0.11.0
     */
    public function copyFromEmptyFolderWithRoot(): void
    {
        $root = vfsStream::setup();
        assertThat(
            vfsStream::copyFromFileSystem($this->fileSystemCopyDir() . '/emptyFolder'),
            isSameAs($root)
        );
        $root->removeChild('.gitignore');
        assertFalse($root->hasChildren());
    }

    /**
     * @test
     * @group  issue_4
     * @since  0.11.0
     */
    public function copyFromWithSubFolders(): void
    {
        $baseDir = vfsStream::copyFromFileSystem(
            $this->fileSystemCopyDir(),
            vfsStream::newDirectory('test'),
            3
        );
        $this->assertFileSystemCopy($baseDir);
    }

    /**
     * @test
     * @group  issue_4
     * @since  0.11.0
     */
    public function copyFromWithSubFoldersWithRoot(): void
    {
        $root = vfsStream::setup();
        vfsStream::copyFromFileSystem($this->fileSystemCopyDir(), null, 3);
        $this->assertFileSystemCopy($root);
    }

    private function assertFileSystemCopy(vfsStreamDirectory $baseDir): void
    {
        assertTrue($baseDir->hasChildren());
        assertTrue($baseDir->hasChild('emptyFolder'));
        assertTrue($baseDir->hasChild('withSubfolders'));
        $subfolderDir = $baseDir->getChild('withSubfolders');
        assertTrue($subfolderDir->hasChild('subfolder1'));
        assertTrue($subfolderDir->getChild('subfolder1')->hasChild('file1.txt'));
        assertThat($subfolderDir->getChild('subfolder1/file1.txt')->getContent(), equals('      '));
        assertTrue($subfolderDir->hasChild('subfolder2'));
        assertTrue($subfolderDir->hasChild('aFile.txt'));
        assertThat($subfolderDir->getChild('aFile.txt')->getContent(), equals('foo'));
    }

    /**
     * @test
     * @group  issue_4
     * @group  issue_29
     * @since  0.11.2
     */
    public function copyFromPreservesFilePermissions(): void
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            $this->markTestSkipped('Only applicable on Linux style systems.');
        }

        $copyDir = $this->fileSystemCopyDir();
        $root = vfsStream::setup();
        vfsStream::copyFromFileSystem($copyDir);
        assertThat(
            $root->getChild('withSubfolders')->getPermissions(),
            equals(fileperms($copyDir . '/withSubfolders') - vfsStreamContent::TYPE_DIR)
        );
        assertThat(
            $root->getChild('withSubfolders/aFile.txt')->getPermissions(),
            equals(fileperms($copyDir . '/withSubfolders/aFile.txt') - vfsStreamContent::TYPE_FILE)
        );
    }

    /**
     * To test this the max file size is reduced to something reproduceable.
     *
     * @test
     * @group  issue_91
     * @since  1.5.0
     */
    public function copyFromFileSystemMocksLargeFiles(): void
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            $this->markTestSkipped('Only applicable on Linux style systems.');
        }

        $copyDir = $this->fileSystemCopyDir();
        $root = vfsStream::setup();
        vfsStream::copyFromFileSystem($copyDir, $root, 3);
        assertThat(
            $root->getChild('withSubfolders/subfolder1/file1.txt')->getContent(),
            equals('      ')
        );
    }

    /**
     * @test
     * @group  issue_121
     * @since  1.6.1
     */
    public function createDirectoryWithTrailingSlashShouldNotCreateSubdirectoryWithEmptyName(): void
    {
        $directory = vfsStream::newDirectory('foo/');
        assertFalse($directory->hasChildren());
    }

    /**
     * @test
     * @group  issue_149
     */
    public function addStructureHandlesVfsStreamFileObjects(): void
    {
        $structure = [
            'topLevel' => [
                'thisIsAFile' => 'file contents',
                //phpcs:ignore Squiz.Arrays.ArrayDeclaration.NoKeySpecified
                vfsStream::newFile('anotherFile'),
            ],
        ];

        vfsStream::setup();
        $root = vfsStream::create($structure);
        assertTrue($root->hasChild('topLevel/anotherFile'));
    }

    /**
     * @test
     * @group  issue_149
     */
    public function createHandlesLargeFileContentObjects(): void
    {
        $structure = [
            'topLevel' => [
                'thisIsAFile' => 'file contents',
                'anotherFile' => LargeFileContent::withMegabytes(2),
            ],
        ];

        vfsStream::setup();
        $root = vfsStream::create($structure);
        assertTrue($root->hasChild('topLevel/anotherFile'));
    }
}
