<?php
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
use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\visitor\vfsStreamVisitor;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assert;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
use function bovigo\callmap\verify;
/**
 * Test for org\bovigo\vfs\vfsStream.
 */
class vfsStreamTestCase extends TestCase
{
    /**
     * set up test environment
     */
    public function setUp()
    {
        vfsStreamWrapper::register();
    }

    public function pathes(): array
    {
        return [
            ['foo', 'vfs://foo'],
            ['foo/bar.baz', 'vfs://foo/bar.baz'],
            ['foo\bar.baz', 'vfs://foo/bar.baz']
        ];
    }

    /**
     * @test
     * @dataProvider pathes
     */
    public function pathToUrlConversion($path, $url)
    {
        assert(vfsStream::url($path), equals($url));
    }

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
            ['vfs://foo//bar', 'foo/bar']
        ];
    }

    /**
     * @test
     * @dataProvider urls
     */
    public function urlToPathConversion($url, $path)
    {
        assert(vfsStream::path($url), equals($path));
    }

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
    public function newDirectoryCreatesStructureWhenNameContainsSlashes($root, $permissions)
    {
        assert($root->getPermissions(), equals($permissions));

        assertTrue($root->hasChild('bar'));
        assertTrue($root->hasChild('bar/baz'));
        assertFalse($root->hasChild('baz'));

        $bar = $root->getChild('bar');
        assert($bar->getPermissions(), equals($permissions));
        assertTrue($bar->hasChild('baz'));
        $baz1 = $bar->getChild('baz');

        assert($baz1->getPermissions(), equals($permissions));
        $baz2 = $root->getChild('bar/baz');
        assert($baz1, isSameAs($baz2));
    }

    /**
     * @test
     * @group  setup
     * @since  0.7.0
     */
    public function setupRegistersStreamWrapper()
    {
        $root = vfsStream::setup();
        assert(vfsStreamWrapper::getRoot(), isSameAs($root));
    }

    /**
     * @test
     * @group  setup
     * @since  0.7.0
     */
    public function setupCreatesRootDirectoryWithDefaultName()
    {
        $root = vfsStream::setup();
        assert($root->getName(), equals('root'));
    }

    /**
     * @test
     * @group  setup
     * @since  0.7.0
     */
    public function setupCreatesRootDirectoryWithDefaultPermissions()
    {
        $root = vfsStream::setup();
        assert($root->getPermissions(), equals(0777));
    }

    /**
     * @test
     * @group  setup
     * @since  0.7.0
     */
    public function setupCreatesRootDirectoryWithGivenNameAn()
    {
        $root = vfsStream::setup('foo');
        assert($root->getName(), equals('foo'));
    }

    /**
     * @test
     * @group  setup
     * @since  0.7.0
     */
    public function setupCreatesRootDirectoryWithPermissions()
    {
        $root = vfsStream::setup('foo', 0444);
        assert($root->getPermissions(), equals(0444));
    }

    /**
     * @test
     * @group  issue_14
     * @group  issue_20
     * @since  0.10.0
     */
    public function setupWithEmptyStructureIsEqualToSetup()
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
    public function setupArraysAreTurnedIntoSubdirectories()
    {
        $root = vfsStream::setup('root', null, ['test' => []]);
        assertTrue($root->hasChildren());
        assertTrue($root->hasChild('test'));
        assert(
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
    public function setupStringsAreTurnedIntoFilesWithContent()
    {
        $root = vfsStream::setup('root', null, ['test.txt' => 'some content']);
        assertTrue($root->hasChildren());
        assertTrue($root->hasChild('test.txt'));
        assert($root->getChild('test.txt')->getContent(), equals('some content'));
    }

    /**
     * @test
     * @group  issue_14
     * @group  issue_20
     * @since  0.10.0
     */
    public function setupWorksRecursively()
    {
        $root = vfsStream::setup(
            'root',
            null,
            ['test' => ['foo'     => ['test.txt' => 'hello'],
                        'baz.txt' => 'world'
                        ]
            ]
        );
        assertTrue($root->hasChildren());
        assertTrue($root->hasChild('test'));
        $test = $root->getChild('test');
        assertTrue($test->hasChildren());
        assertTrue($test->hasChild('baz.txt'));
        assert($test->getChild('baz.txt')->getContent(), equals('world'));

        assertTrue($test->hasChild('foo'));
        $foo = $test->getChild('foo');
        assertTrue($foo->hasChildren());
        assertTrue($foo->hasChild('test.txt'));
        assert($foo->getChild('test.txt')->getContent(), equals('hello'));
    }

    /**
    * @test
    * @group  issue_17
    * @group  issue_20
    */
    public function setupCastsNumericDirectoriesToStrings()
    {
        $root = vfsStream::setup(
            'root',
            null,
            [2011 => ['test.txt' => 'some content']]
        );
        assertTrue($root->hasChild('2011'));

        $directory = $root->getChild('2011');
        assert($directory->getChild('test.txt')->getContent(), equals('some content'));

        assertTrue(file_exists('vfs://root/2011/test.txt'));
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createArraysAreTurnedIntoSubdirectories()
    {
        $baseDir = vfsStream::create(['test' => []], vfsStream::newDirectory('baseDir'));
        assertTrue($baseDir->hasChildren());
        assertTrue($baseDir->hasChild('test'));
        assert($baseDir->getChild('test'), isInstanceOf(vfsStreamDirectory::class));
        assertFalse($baseDir->getChild('test')->hasChildren());
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createArraysAreTurnedIntoSubdirectoriesOfRoot()
    {
        $root = vfsStream::setup();
        assert(vfsStream::create(['test' => []]), isSameAs($root));
        assertTrue($root->hasChildren());
        assertTrue($root->hasChild('test'));
        assert(
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
    public function createThrowsExceptionIfNoBaseDirGivenAndNoRootSet()
    {
        expect(function() { vfsStream::create(['test' => []]); })
          ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createWorksRecursively()
    {
        $baseDir = vfsStream::create(
            ['test' => ['foo'     => ['test.txt' => 'hello'],
                        'baz.txt' => 'world'
                       ]
            ],
            vfsStream::newDirectory('baseDir')
        );
        assertTrue($baseDir->hasChildren());
        assertTrue($baseDir->hasChild('test'));
        $test = $baseDir->getChild('test');

        assertTrue($test->hasChildren());
        assertTrue($test->hasChild('baz.txt'));
        assert($test->getChild('baz.txt')->getContent(), equals('world'));

        assertTrue($test->hasChild('foo'));
        $foo = $test->getChild('foo');

        assertTrue($foo->hasChildren());
        assertTrue($foo->hasChild('test.txt'));
        assert($foo->getChild('test.txt')->getContent(), equals('hello'));
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createWorksRecursivelyWithRoot()
    {
        $root = vfsStream::setup();
        assert(
            vfsStream::create([
              'test' => ['foo'     => ['test.txt' => 'hello'],
                         'baz.txt' => 'world'
                        ]
            ]),
            isSameAs($root)
        );
        assertTrue($root->hasChildren());
        assertTrue($root->hasChild('test'));
        $test = $root->getChild('test');
        assertTrue($test->hasChildren());
        assertTrue($test->hasChild('baz.txt'));
        assert($test->getChild('baz.txt')->getContent(), equals('world'));

        assertTrue($test->hasChild('foo'));
        $foo = $test->getChild('foo');
        assertTrue($foo->hasChildren());
        assertTrue($foo->hasChild('test.txt'));
        assert($foo->getChild('test.txt')->getContent(), equals('hello'));
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.10.0
     */
    public function createStringsAreTurnedIntoFilesWithContent()
    {
        $baseDir = vfsStream::create(
            ['test.txt' => 'some content'],
            vfsStream::newDirectory('baseDir')
        );
        assertTrue($baseDir->hasChildren());
        assertTrue($baseDir->hasChild('test.txt'));
        assert($baseDir->getChild('test.txt')->getContent(), equals('some content'));
    }

    /**
     * @test
     * @group  issue_20
     * @since  0.11.0
     */
    public function createStringsAreTurnedIntoFilesWithContentWithRoot()
    {
        $root = vfsStream::setup();
        vfsStream::create(['test.txt' => 'some content']);
        assertTrue($root->hasChildren());
        assertTrue($root->hasChild('test.txt'));
        assert($root->getChild('test.txt')->getContent(), equals('some content'));
    }

    /**
    * @test
    * @group  issue_20
    * @since  0.11.0
    */
    public function createCastsNumericDirectoriesToStrings()
    {
        $baseDir = vfsStream::create(
            [2011 => ['test.txt' => 'some content']],
            vfsStream::newDirectory('baseDir')
        );
        assertTrue($baseDir->hasChild('2011'));

        $directory = $baseDir->getChild('2011');
        assert($directory->getChild('test.txt')->getContent(), equals('some content'));
    }

    /**
    * @test
    * @group  issue_20
    * @since  0.11.0
    */
    public function createCastsNumericDirectoriesToStringsWithRoot()
    {
        $root = vfsStream::setup();
        vfsStream::create([2011 => ['test.txt' => 'some content']]);
        assertTrue($root->hasChild('2011'));

        $directory = $root->getChild('2011');
        assert($directory->getChild('test.txt')->getContent(), equals('some content'));
    }

    /**
     * @test
     * @group  issue_10
     * @since  0.10.0
     */
    public function inspectReturnsGivenVisitor()
    {
        $content = NewInstance::of(vfsStreamContent::class);
        $visitor = NewInstance::of(vfsStreamVisitor::class);
        assert(vfsStream::inspect($visitor, $content), isSameAs($visitor));
    }

    /**
     * @test
     * @group  issue_10
     * @since  0.10.0
     */
    public function inspectWithContentGivesContentToVisitor()
    {
        $content = NewInstance::of(vfsStreamContent::class);
        $visitor = NewInstance::of(vfsStreamVisitor::class);
        vfsStream::inspect($visitor, $content);
        verify($visitor, 'visit')->received($content);
    }

    /**
     * @test
     * @group  issue_10
     * @since  0.10.0
     */
    public function inspectWithoutContentGivesRootToVisitor()
    {
        $root    = vfsStream::setup();
        $visitor = NewInstance::of(vfsStreamVisitor::class);
        vfsStream::inspect($visitor);
        verify($visitor, 'visitDirectory')->received($root);
    }

    /**
     * @test
     * @group  issue_10
     * @since  0.10.0
     */
    public function inspectWithoutContentAndWithoutRootThrowsInvalidArgumentException()
    {
        $visitor = NewInstance::of(vfsStreamVisitor::class);
        expect(function() use ($visitor) { vfsStream::inspect($visitor); })
          ->throws(\InvalidArgumentException::class);
        verify($visitor, 'visit')->wasNeverCalled();
        verify($visitor, 'visitDirectory')->wasNeverCalled();
    }

    private function fileSystemCopyDir(): string
    {
        return realpath(dirname(__FILE__) . '/../../../../resources/filesystemcopy');
    }

    /**
     * @test
     * @group  issue_4
     * @since  0.11.0
     */
    public function copyFromFileSystemThrowsExceptionIfNoBaseDirGivenAndNoRootSet()
    {
        expect(function() {
            vfsStream::copyFromFileSystem($this->fileSystemCopyDir());
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     * @group  issue_4
     * @since  0.11.0
     */
    public function copyFromEmptyFolder()
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
    public function copyFromEmptyFolderWithRoot()
    {
        $root = vfsStream::setup();
        assert(
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
    public function copyFromWithSubFolders()
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
    public function copyFromWithSubFoldersWithRoot()
    {
        $root = vfsStream::setup();
        vfsStream::copyFromFileSystem($this->fileSystemCopyDir(), null, 3);
        $this->assertFileSystemCopy($root);
    }

    private function assertFileSystemCopy(vfsStreamDirectory $baseDir)
    {
      assertTrue($baseDir->hasChildren());
      assertTrue($baseDir->hasChild('emptyFolder'));
      assertTrue($baseDir->hasChild('withSubfolders'));
      $subfolderDir = $baseDir->getChild('withSubfolders');
      assertTrue($subfolderDir->hasChild('subfolder1'));
      assertTrue($subfolderDir->getChild('subfolder1')->hasChild('file1.txt'));
      assert($subfolderDir->getChild('subfolder1/file1.txt')->getContent(), equals('      '));
      assertTrue($subfolderDir->hasChild('subfolder2'));
      assertTrue($subfolderDir->hasChild('aFile.txt'));
      assert($subfolderDir->getChild('aFile.txt')->getContent(), equals('foo'));
    }

    /**
     * @test
     * @group  issue_4
     * @group  issue_29
     * @since  0.11.2
     */
    public function copyFromPreservesFilePermissions()
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            $this->markTestSkipped('Only applicable on Linux style systems.');
        }

        $copyDir = $this->fileSystemCopyDir();
        $root    = vfsStream::setup();
        vfsStream::copyFromFileSystem($copyDir);
        assert(
            $root->getChild('withSubfolders')->getPermissions(),
            equals(fileperms($copyDir . '/withSubfolders') - vfsStreamContent::TYPE_DIR)
        );
        assert(
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
    public function copyFromFileSystemMocksLargeFiles()
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            $this->markTestSkipped('Only applicable on Linux style systems.');
        }

        $copyDir = $this->fileSystemCopyDir();
        $root    = vfsStream::setup();
        vfsStream::copyFromFileSystem($copyDir, $root, 3);
        assert(
            $root->getChild('withSubfolders/subfolder1/file1.txt')->getContent(),
            equals('      ')
        );
    }

    /**
     * @test
     * @group  issue_121
     * @since  1.6.1
     */
    public function createDirectoryWithTrailingSlashShouldNotCreateSubdirectoryWithEmptyName()
    {
        $directory = vfsStream::newDirectory('foo/');
        assertFalse($directory->hasChildren());
    }

    /**
     * @test
     * @group  issue_149
     */
    public function addStructureHandlesVfsStreamFileObjects()
    {
        $structure = [
            'topLevel' => [
                'thisIsAFile' => 'file contents',
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
    public function createHandlesLargeFileContentObjects()
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
