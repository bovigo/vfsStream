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

use function bovigo\assert\{
    assertThat,
    assertFalse,
    assertNotNull,
    assertNull,
    assertTrue,
    expect,
    predicate\equals,
    predicate\isExistingDirectory,
    predicate\isOfSize,
    predicate\isSameAs
};
/**
 * Test for org\bovigo\vfs\vfsStreamWrapper around mkdir().
 */
class vfsStreamWrapperMkDirTestCase extends vfsStreamWrapperBaseTestCase
{
    public function newRoots(): array
    {
        return [
            ['another'],
            ['another/more']
        ];
    }

    /**
     * @test
     * @dataProvider  newRoots
     */
    public function mkdirDoesNotOverwriteExistingRoot($newRoot)
    {
        assertFalse(mkdir(vfsStream::url($newRoot), 0777, true));
        assertThat(vfsStreamWrapper::getRoot(), isSameAs($this->root));
    }

    /**
     * @test
     * @group  permissions
     */
    public function mkdirNonRecursivelyIsRejectedWhenNotSpecified()
    {
        assertFalse(mkdir($this->subdir->url() . '/another/more'));
        assertFalse($this->root->hasChild('another'));
    }

    /**
     * @test
     * @group  permissions
     */
    public function mkdirNonRecursivelyForSingleDirectory()
    {
        assertTrue(mkdir($this->root->url() . '/another'));
        assertTrue($this->root->hasChild('another'));
    }

    /**
     * @test
     * @group  permissions
     */
    public function mkdirNonRecursivelyWithDefaultPermissions()
    {
        assertTrue(mkdir($this->root->url() . '/another'));
        assertThat($this->root->getChild('another')->getPermissions(), equals(0777));
    }

    public function mkdirChildren(): array
    {
        return [['another'], ['another/more']];
    }

    /**
     * @test
     * @dataProvider  mkdirChildren
     * @group  permissions
     */
    public function mkdirRecursively(string $child)
    {
        assertTrue(mkdir($this->root->url() . '/another/more', 0775, true));
        assertTrue($this->root->hasChild($child));
        assertThat($this->root->getChild($child)->getPermissions(), equals(0775));
    }

    /**
     * @test
     * @group  issue_9
     * @since  0.9.0
     */
    public function mkdirWithDots()
    {
        assertTrue(mkdir($this->root->url() . '/another/../more/.', 0777, true));
        assertTrue($this->root->hasChild('more'));
    }

    /**
     * @test
     * @group  permissions
     */
    public function mkdirWithoutRootCreatesNewRoot()
    {
        vfsStreamWrapper::register();
        assertTrue(@mkdir(vfsStream::url('root')));
        $root = vfsStreamWrapper::getRoot();
        assertThat($root->getName(), equals('root'));
        assertThat($root->getPermissions(), equals(0777));
    }

    /**
     * @test
     * @group  permissions
     */
    public function mkdirWithoutRootCreatesNewRootDifferentPermissions()
    {
        vfsStreamWrapper::register();
        assertTrue(@mkdir(vfsStream::url('root'), 0755));
        $root = vfsStreamWrapper::getRoot();
        assertThat($root->getName(), equals('root'));
        assertThat($root->getPermissions(), equals(0755));
    }

    /**
     * @test
     */
    public function mkdirOnExistingFileReturnsFalse()
    {
        assertFalse(mkdir($this->fileInSubdir->url() . '/another/more', 0777, true));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function mkdirDirCanNotCreateNewDirInNonWritingDirectory()
    {
        vfsStream::newDirectory('restrictedFolder', 0000)->at($this->root);
        assertFalse(mkdir(vfsStream::url('root/restrictedFolder/newFolder')));
        assertFalse($this->root->hasChild('restrictedFolder/newFolder'));
    }

    /**
     * @test
     * @group  issue_28
     */
    public function mkDirShouldNotOverwriteExistingDirectories()
    {
        $dir = vfsStream::url('root/dir');
        assertTrue(mkdir($dir));
        assertFalse(@mkdir($dir));
    }

    /**
     * @test
     * @group  issue_28
     */
    public function mkDirShouldNotOverwriteExistingDirectoriesAndTriggerE_USER_WARNING()
    {
        $dir = vfsStream::url('root/dir');
        mkdir($dir);
        expect(function() use ($dir) { mkdir($dir); })
          ->triggers(E_USER_WARNING)
          ->withMessage('mkdir(): Path vfs://root/dir exists');
    }

    /**
     * @test
     * @group  issue_28
     */
    public function mkDirShouldNotOverwriteExistingFiles()
    {
        assertFalse(@mkdir($this->fileInRoot->url()));
    }

    /**
     * @test
     * @group  issue_28
     */
    public function mkDirShouldNotOverwriteExistingFilesAndTriggerE_USER_WARNING()
    {
        expect(function() { mkdir($this->fileInRoot->url()); })
          ->triggers(E_USER_WARNING)
          ->withMessage('mkdir(): Path vfs://root/file2 exists');
    }

    /**
     * @test
     * @group  issue_131
     * @since  1.6.3
     */
    public function allowsRecursiveMkDirWithDirectoryName0()
    {
        $subdir  = vfsStream::url('root/a/0');
        mkdir($subdir, 0777, true);
        assertThat($subdir, isExistingDirectory());
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function canNotIterateOverNonReadableDirectory()
    {
        $restricted = vfsStream::newDirectory('restrictedFolder', 0000)->at($this->root);
        assertFalse(@opendir($restricted->url()));
        assertFalse(@dir($restricted->url()));
    }

    public function directories(): array
    {
        return [
            [vfsStream::url('root')],
            [vfsStream::url('root') . '/.'],
            [vfsStream::url('root/subdir')],
            [vfsStream::url('root/subdir') . '/.'],
        ];
    }

    /**
     * @test
     * @dataProvider directories
     */
    public function is_dirReturnsTrueForDirectories(string $directory)
    {
        assertTrue(is_dir($directory));
    }

    public function nonDirectories(): array
    {
        return [
            [vfsStream::url('root/subdir/file1.txt')],
            [vfsStream::url('root/file2')],
            [vfsStream::url('root/annother')],
        ];
    }

    /**
     * @test
     * @dataProvider nonDirectories
     */
    public function is_dirReturnsFalseForFilesAndNonExistingDirectories(string $file)
    {
        assertFalse(is_dir($file));
    }

    /**
     * @test
     */
    public function canNotUnlinkDirectoryWithoutRoot()
    {
        vfsStreamWrapper::register();
        assertFalse(@rmdir(vfsStream::url('root')));
    }

    /**
     * @test
     */
    public function rmdirCanNotRemoveFiles()
    {
        assertFalse(rmdir($this->fileInSubdir->url()));
    }

    /**
     * @test
     */
    public function rmdirCanNotRemoveNonExistingDirectory()
    {
        assertFalse(rmdir($this->root->url() . '/another'));
    }

    /**
     * @test
     */
    public function rmdirCanNotRemoveNonEmptyDirectory()
    {
        assertFalse(rmdir($this->root->url()));
    }

    /**
     * @test
     */
    public function rmdirCanRemoveEmptyDirectory()
    {
        vfsStream::newDirectory('empty')->at($this->root);
        assertTrue(rmdir($this->root->url() . '/empty'));
        assertFalse($this->root->hasChild('empty'));
    }

    /**
     * @test
     */
    public function rmdirCanRemoveEmptyDirectoryWithDot()
    {
        vfsStream::newDirectory('empty')->at($this->root);
        assertTrue(rmdir($this->root->url() . '/empty/.'));
        assertFalse($this->root->hasChild('empty'));
    }

    /**
     * @test
     */
    public function rmdirCanRemoveEmptyRoot()
    {
        $this->root->removeChild('subdir');
        $this->root->removeChild('file2');
        assertTrue(rmdir($this->root->url()));
        assertFalse(file_exists($this->root->url())); // make sure statcache was cleared
        assertNull(vfsStreamWrapper::getRoot());
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function rmdirDirCanNotRemoveDirFromNonWritingDirectory()
    {
        $nonRemovable = vfsStream::newDirectory('nonRemovableFolder')->at($this->root);
        $this->root->chmod(0000);
        assertFalse(rmdir($nonRemovable->url()));
        assertTrue($this->root->hasChild('nonRemovableFolder'));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_17
     */
    public function issue17()
    {
        $this->root->chmod(0770)
            ->chgrp(vfsStream::GROUP_USER_1)
            ->chown(vfsStream::OWNER_USER_1);
        assertFalse(mkdir(vfsStream::url('root/doesNotWork')));
        assertFalse($this->root->hasChild('doesNotWork'));
    }

    /**
     * @test
     * @group  bug_19
     */
    public function accessWithDoubleDotReturnsCorrectContent()
    {
        assertThat(
            file_get_contents(vfsStream::url('root/subdir/../file2')),
            equals('file 2')
        );
    }

    /**
     * @test
     * @group bug_115
     */
    public function accessWithExcessDoubleDotsReturnsCorrectContent()
    {
        assertThat(
            file_get_contents(vfsStream::url('root/../../../../subdir/../file2')),
            equals('file 2')
        );
    }

    /**
     * @test
     * @group bug_115
     */
    public function alwaysResolvesRootDirectoryAsOwnParentWithDoubleDot()
    {
        $this->root->chown(vfsStream::OWNER_USER_1);
        assertTrue(is_dir(vfsStream::url('root/..')));
        $stat = stat(vfsStream::url('root/..'));
        assertThat($stat['uid'], equals(vfsStream::OWNER_USER_1));
    }


    /**
     * @test
     * @since  0.11.0
     * @group  issue_23
     */
    public function unlinkCanNotRemoveNonEmptyDirectory()
    {
        expect(function() { assertFalse(unlink($this->subdir->url())); })
          ->triggers()
          ->withMessage('unlink(vfs://root/subdir): Operation not permitted')
          ->after($this->subdir->url(), isExistingDirectory());
    }

    /**
     * @test
     * @since  0.11.0
     * @group  issue_23
     */
    public function unlinkCanNotRemoveEmptyDirectory()
    {
        $url = vfsStream::newDirectory('empty')->at($this->root)->url();
        expect(function() use ($url) { assertFalse(unlink($url)); })
          ->triggers()
          ->withMessage('unlink(vfs://root/empty): Operation not permitted')
          ->after($this->root->url() . '/empty', isExistingDirectory());
    }

    /**
     * @test
     * @group  issue_32
     */
    public function canCreateFolderOfSameNameAsParentFolder()
    {
        $root = vfsStream::setup('testFolder');
        mkdir(vfsStream::url('testFolder') . '/testFolder/subTestFolder', 0777, true);
        assertTrue(file_exists(vfsStream::url('testFolder/testFolder/subTestFolder/.')));
    }

    /**
     * @test
     * @group  issue_32
     */
    public function canRetrieveFolderOfSameNameAsParentFolder()
    {
        $root = vfsStream::setup('testFolder');
        mkdir(vfsStream::url('testFolder') . '/testFolder/subTestFolder', 0777, true);
        assertTrue($root->hasChild('testFolder'));
        assertNotNull($root->getChild('testFolder'));
    }
}
