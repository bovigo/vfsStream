<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamContent;
use bovigo\vfs\vfsDirectory;
use bovigo\vfs\StreamWrapper;
use const E_USER_WARNING;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertNotNull;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isExistingDirectory;
use function bovigo\assert\predicate\isSameAs;
use function dir;
use function file_exists;
use function file_get_contents;
use function is_dir;
use function mkdir;
use function opendir;
use function rmdir;
use function stat;
use function unlink;

/**
 * Test for bovigo\vfs\vfsStreamWrapper around mkdir().
 */
class vfsStreamWrapperMkDirTestCase extends vfsStreamWrapperBaseTestCase
{
    /**
     * mkdir() should not overwrite existing root
     *
     * @test
     */
    public function mkdirNoNewRoot()
    {
        $this->assertFalse(mkdir(vfsStream::url('another')));
        $this->assertEquals(2, count($this->foo->getChildren()));
        $this->assertSame($this->foo, StreamWrapper::getRoot());
    }

    /**
     * mkdir() should not overwrite existing root
     *
     * @test
     */
    public function mkdirNoNewRootRecursively()
    {
        $this->assertFalse(mkdir(vfsStream::url('another/more'), 0777, true));
        $this->assertEquals(2, count($this->foo->getChildren()));
        $this->assertSame($this->foo, StreamWrapper::getRoot());
    }

    /**
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     * @group  permissions
     */
    public function mkdirNonRecursively()
    {
        $this->assertFalse(mkdir($this->barURL . '/another/more'));
        $this->assertEquals(2, count($this->foo->getChildren()));
        $this->assertTrue(mkdir($this->fooURL . '/another'));
        $this->assertEquals(3, count($this->foo->getChildren()));
        $this->assertEquals(0777, $this->foo->getChild('another')->getPermissions());
    }

    /**
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     * @group  permissions
     */
    public function mkdirRecursively()
    {
        $this->assertTrue(mkdir($this->fooURL . '/another/more', 0777, true));
        $this->assertEquals(3, count($this->foo->getChildren()));
        $another = $this->foo->getChild('another');
        $this->assertTrue($another->hasChild('more'));
        $this->assertEquals(0777, $this->foo->getChild('another')->getPermissions());
        $this->assertEquals(0777, $this->foo->getChild('another')->getChild('more')->getPermissions());
    }

    /**
     * @test
     * @group  issue_9
     * @since  0.9.0
     */
    public function mkdirWithDots()
    {
        $this->assertTrue(mkdir($this->fooURL . '/another/../more/.', 0777, true));
        $this->assertEquals(3, count($this->foo->getChildren()));
        $this->assertTrue($this->foo->hasChild('more'));
    }

    /**
     * no root > new directory becomes root
     *
     * @test
     * @group  permissions
     */
    public function mkdirWithoutRootCreatesNewRoot()
    {
        StreamWrapper::register();
        $this->assertTrue(@mkdir(vfsStream::url('foo')));
        $this->assertEquals(vfsStreamContent::TYPE_DIR, StreamWrapper::getRoot()->getType());
        $this->assertEquals('foo', StreamWrapper::getRoot()->getName());
        $this->assertEquals(0777, StreamWrapper::getRoot()->getPermissions());
    }

    /**
     * trying to create a subdirectory of a file should not work
     *
     * @test
     */
    public function mkdirOnFileReturnsFalse()
    {
        $this->assertFalse(mkdir($this->baz1URL . '/another/more', 0777, true));
    }

    /**
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     * @group  permissions
     */
    public function mkdirNonRecursivelyDifferentPermissions()
    {
        $this->assertTrue(mkdir($this->fooURL . '/another', 0755));
        $this->assertEquals(0755, $this->foo->getChild('another')->getPermissions());
    }

    /**
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     * @group  permissions
     */
    public function mkdirRecursivelyDifferentPermissions()
    {
        $this->assertTrue(mkdir($this->fooURL . '/another/more', 0755, true));
        $this->assertEquals(3, count($this->foo->getChildren()));
        $another = $this->foo->getChild('another');
        $this->assertTrue($another->hasChild('more'));
        $this->assertEquals(0755, $this->foo->getChild('another')->getPermissions());
        $this->assertEquals(0755, $this->foo->getChild('another')->getChild('more')->getPermissions());
    }

    /**
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     * @group  permissions
     */
    public function mkdirRecursivelyUsesDefaultPermissions()
    {
        $this->foo->chmod(0700);
        $this->assertTrue(mkdir($this->fooURL . '/another/more', 0777, true));
        $this->assertEquals(3, count($this->foo->getChildren()));
        $another = $this->foo->getChild('another');
        $this->assertTrue($another->hasChild('more'));
        $this->assertEquals(0777, $this->foo->getChild('another')->getPermissions());
        $this->assertEquals(0777, $this->foo->getChild('another')->getChild('more')->getPermissions());
    }

    /**
     * no root > new directory becomes root
     *
     * @test
     * @group  permissions
     */
    public function mkdirWithoutRootCreatesNewRootDifferentPermissions()
    {
        StreamWrapper::register();
        $this->assertTrue(@mkdir(vfsStream::url('foo'), 0755));
        $this->assertEquals(vfsStreamContent::TYPE_DIR, StreamWrapper::getRoot()->getType());
        $this->assertEquals('foo', StreamWrapper::getRoot()->getName());
        $this->assertEquals(0755, StreamWrapper::getRoot()->getPermissions());
    }

    /**
     * no root > new directory becomes root
     *
     * @test
     * @group  permissions
     */
    public function mkdirWithoutRootCreatesNewRootWithDefaultPermissions()
    {
        StreamWrapper::register();
        $this->assertTrue(@mkdir(vfsStream::url('foo')));
        $this->assertEquals(vfsStreamContent::TYPE_DIR, StreamWrapper::getRoot()->getType());
        $this->assertEquals('foo', StreamWrapper::getRoot()->getName());
        $this->assertEquals(0777, StreamWrapper::getRoot()->getPermissions());
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function mkdirDirCanNotCreateNewDirInNonWritingDirectory()
    {
        StreamWrapper::register();
        StreamWrapper::setRoot(new vfsDirectory('root'));
        StreamWrapper::getRoot()->addChild(new vfsDirectory('restrictedFolder', 0000));
        $this->assertFalse(is_writable(vfsStream::url('root/restrictedFolder/')));
        $this->assertFalse(mkdir(vfsStream::url('root/restrictedFolder/newFolder')));
        $this->assertFalse(StreamWrapper::getRoot()->hasChild('restrictedFolder/newFolder'));
    }

    /**
     * @test
     * @group  issue_28
     */
    public function mkDirShouldNotOverwriteExistingDirectories()
    {
        vfsStream::setup('root');
        $dir = vfsStream::url('root/dir');
        $this->assertTrue(mkdir($dir));
        $this->assertFalse(@mkdir($dir));
    }

    /**
     * @test
     * @group  issue_28
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessage  mkdir(): Path vfs://root/dir exists
     */
    public function mkDirShouldNotOverwriteExistingDirectoriesAndTriggerE_USER_WARNING()
    {
        vfsStream::setup('root');
        $dir = vfsStream::url('root/dir');
        $this->assertTrue(mkdir($dir));
        $this->assertFalse(mkdir($dir));
    }

    /**
     * @test
     * @group  issue_28
     */
    public function mkDirShouldNotOverwriteExistingFiles()
    {
        $root = vfsStream::setup('root');
        vfsStream::newFile('test.txt')->at($root);
        $this->assertFalse(@mkdir(vfsStream::url('root/test.txt')));
    }

    /**
     * @test
     * @group  issue_28
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessage  mkdir(): Path vfs://root/test.txt exists
     */
    public function mkDirShouldNotOverwriteExistingFilesAndTriggerE_USER_WARNING()
    {
        $root = vfsStream::setup('root');
        vfsStream::newFile('test.txt')->at($root);
        $this->assertFalse(mkdir(vfsStream::url('root/test.txt')));
    }

    /**
     * @test
     * @group  issue_131
     * @since  1.6.3
     */
    public function allowsRecursiveMkDirWithDirectoryName0()
    {
        vfsStream::setup('root');
        $subdir  = vfsStream::url('root/a/0');
        mkdir($subdir, 0777, true);
        $this->assertFileExists($subdir);
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function canNotIterateOverNonReadableDirectory()
    {
        StreamWrapper::register();
        StreamWrapper::setRoot(new vfsDirectory('root', 0000));
        $this->assertFalse(@opendir(vfsStream::url('root')));
        $this->assertFalse(@dir(vfsStream::url('root')));
    }

    /**
     * assert is_dir() returns correct result
     *
     * @test
     */
    public function is_dir()
    {
        $this->assertTrue(is_dir($this->fooURL));
        $this->assertTrue(is_dir($this->fooURL . '/.'));
        $this->assertTrue(is_dir($this->barURL));
        $this->assertTrue(is_dir($this->barURL . '/.'));
        $this->assertFalse(is_dir($this->baz1URL));
        $this->assertFalse(is_dir($this->baz2URL));
        $this->assertFalse(is_dir($this->fooURL . '/another'));
        $this->assertFalse(is_dir(vfsStream::url('another')));
    }

    /**
     * can not unlink without root
     *
     * @test
     */
    public function canNotUnlinkDirectoryWithoutRoot()
    {
        StreamWrapper::register();
        $this->assertFalse(@rmdir(vfsStream::url('foo')));
    }

    /**
     * rmdir() can not remove files
     *
     * @test
     */
    public function rmdirCanNotRemoveFiles()
    {
        $this->assertFalse(rmdir($this->baz1URL));
        $this->assertFalse(rmdir($this->baz2URL));
    }

    /**
     * rmdir() can not remove a non-existing directory
     *
     * @test
     */
    public function rmdirCanNotRemoveNonExistingDirectory()
    {
        $this->assertFalse(rmdir($this->fooURL . '/another'));
    }

    /**
     * rmdir() can not remove non-empty directories
     *
     * @test
     */
    public function rmdirCanNotRemoveNonEmptyDirectory()
    {
        $this->assertFalse(rmdir($this->fooURL));
        $this->assertFalse(rmdir($this->barURL));
    }

    /**
     * @test
     */
    public function rmdirCanRemoveEmptyDirectory()
    {
        vfsStream::newDirectory('empty')->at($this->foo);
        $this->assertTrue($this->foo->hasChild('empty'));
        $this->assertTrue(rmdir($this->fooURL . '/empty'));
        $this->assertFalse($this->foo->hasChild('empty'));
    }

    /**
     * @test
     */
    public function rmdirCanRemoveEmptyDirectoryWithDot()
    {
        vfsStream::newDirectory('empty')->at($this->foo);
        $this->assertTrue($this->foo->hasChild('empty'));
        $this->assertTrue(rmdir($this->fooURL . '/empty/.'));
        $this->assertFalse($this->foo->hasChild('empty'));
    }

    /**
     * rmdir() can remove empty directories
     *
     * @test
     */
    public function rmdirCanRemoveEmptyRoot()
    {
        $this->foo->removeChild('bar');
        $this->foo->removeChild('baz2');
        $this->assertTrue(rmdir($this->fooURL));
        $this->assertFalse(file_exists($this->fooURL)); // make sure statcache was cleared
        $this->assertNull(StreamWrapper::getRoot());
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function rmdirDirCanNotRemoveDirFromNonWritingDirectory()
    {
        StreamWrapper::register();
        StreamWrapper::setRoot(new vfsDirectory('root', 0000));
        StreamWrapper::getRoot()->addChild(new vfsDirectory('nonRemovableFolder'));
        $this->assertFalse(is_writable(vfsStream::url('root')));
        $this->assertFalse(rmdir(vfsStream::url('root/nonRemovableFolder')));
        $this->assertTrue(StreamWrapper::getRoot()->hasChild('nonRemovableFolder'));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_17
     */
    public function issue17()
    {
        StreamWrapper::register();
        StreamWrapper::setRoot(new vfsDirectory('root', 0770));
        StreamWrapper::getRoot()->chgrp(vfsStream::GROUP_USER_1)
                                   ->chown(vfsStream::OWNER_USER_1);
        $this->assertFalse(mkdir(vfsStream::url('root/doesNotWork')));
        $this->assertFalse(StreamWrapper::getRoot()->hasChild('doesNotWork'));
    }

    /**
     * @test
     * @group  bug_19
     */
    public function accessWithDoubleDotReturnsCorrectContent()
    {
        $this->assertEquals('baz2',
                file_get_contents(vfsStream::url('foo/bar/../baz2'))
        );
    }

    /**
     * @test
     * @group bug_115
     */
    public function accessWithExcessDoubleDotsReturnsCorrectContent()
    {
        $this->assertEquals('baz2',
            file_get_contents(vfsStream::url('foo/../../../../bar/../baz2'))
        );
    }

    /**
     * @test
     * @group bug_115
     */
    public function alwaysResolvesRootDirectoryAsOwnParentWithDoubleDot()
    {
        StreamWrapper::getRoot()->chown(vfsStream::OWNER_USER_1);

        $this->assertTrue(is_dir(vfsStream::url('foo/..')));
        $stat = stat(vfsStream::url('foo/..'));
        $this->assertEquals(
            vfsStream::OWNER_USER_1,
            $stat['uid']
        );
    }


    /**
     * @test
     * @since  0.11.0
     * @group  issue_23
     */
    public function unlinkCanNotRemoveNonEmptyDirectory()
    {
        try {
            $this->assertFalse(unlink($this->barURL));
        } catch (\PHPUnit_Framework_Error $fe) {
            $this->assertEquals('unlink(vfs://foo/bar): Operation not permitted', $fe->getMessage());
        }

        $this->assertTrue($this->foo->hasChild('bar'));
        $this->assertFileExists($this->barURL);
    }

    /**
     * @test
     * @since  0.11.0
     * @group  issue_23
     */
    public function unlinkCanNotRemoveEmptyDirectory()
    {
        vfsStream::newDirectory('empty')->at($this->foo);
        try {
            $this->assertTrue(unlink($this->fooURL . '/empty'));
        } catch (\PHPUnit_Framework_Error $fe) {
            $this->assertEquals('unlink(vfs://foo/empty): Operation not permitted', $fe->getMessage());
        }

        $this->assertTrue($this->foo->hasChild('empty'));
        $this->assertFileExists($this->fooURL . '/empty');
    }

    /**
     * @test
     * @group  issue_32
     */
    public function canCreateFolderOfSameNameAsParentFolder()
    {
        $root = vfsStream::setup('testFolder');
        mkdir(vfsStream::url('testFolder') . '/testFolder/subTestFolder', 0777, true);
        $this->assertTrue(file_exists(vfsStream::url('testFolder/testFolder/subTestFolder/.')));
    }

    /**
     * @test
     * @group  issue_32
     */
    public function canRetrieveFolderOfSameNameAsParentFolder()
    {
        $root = vfsStream::setup('testFolder');
        mkdir(vfsStream::url('testFolder') . '/testFolder/subTestFolder', 0777, true);
        $this->assertTrue($root->hasChild('testFolder'));
        $this->assertNotNull($root->getChild('testFolder'));
    }
}
