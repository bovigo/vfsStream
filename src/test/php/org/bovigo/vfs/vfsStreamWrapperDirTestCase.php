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
require_once __DIR__ . '/vfsStreamWrapperBaseTestCase.php';

use function bovigo\assert\assert;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertNotNull;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isSameAs;
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
        assert(vfsStreamWrapper::getRoot(), isSameAs($this->foo));
    }

    /**
     * @test
     * @group  permissions
     */
    public function mkdirNonRecursively()
    {
        assertFalse(mkdir($this->barURL . '/another/more'));
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
     * @test
     * @group  permissions
     */
    public function mkdirWithoutRootCreatesNewRoot()
    {
        vfsStreamWrapper::register();
        assertTrue(@mkdir(vfsStream::url('foo')));
        $root = vfsStreamWrapper::getRoot();
        assert($root->getName(), equals('foo'));
        assert($root->getPermissions(), equals(0777));
    }

    /**
     * @test
     * @group  permissions
     */
    public function mkdirWithoutRootCreatesNewRootDifferentPermissions()
    {
        vfsStreamWrapper::register();
        assertTrue(@mkdir(vfsStream::url('foo'), 0755));
        $root = vfsStreamWrapper::getRoot();
        assert($root->getName(), equals('foo'));
        assert($root->getPermissions(), equals(0755));
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
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function mkdirDirCanNotCreateNewDirInNonWritingDirectory()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));
        vfsStreamWrapper::getRoot()->addChild(new vfsStreamDirectory('restrictedFolder', 0000));
        $this->assertFalse(is_writable(vfsStream::url('root/restrictedFolder/')));
        $this->assertFalse(mkdir(vfsStream::url('root/restrictedFolder/newFolder')));
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('restrictedFolder/newFolder'));
    }

    /**
     * @test
     * @group  issue_28
     */
    public function mkDirShouldNotOverwriteExistingDirectories()
    {
        vfsStream::setup('root');
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
        vfsStream::setup('root');
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
        $root = vfsStream::setup('root');
        vfsStream::newFile('test.txt')->at($root);
        assertFalse(@mkdir(vfsStream::url('root/test.txt')));
    }

    /**
     * @test
     * @group  issue_28
     */
    public function mkDirShouldNotOverwriteExistingFilesAndTriggerE_USER_WARNING()
    {
        vfsStream::newFile('test.txt')->at(vfsStream::setup('root'));
        expect(function() { mkdir(vfsStream::url('root/test.txt')); })
          ->triggers(E_USER_WARNING)
          ->withMessage('mkdir(): Path vfs://root/test.txt exists');
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
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(vfsStream::newDirectory('root', 0000));
        assertFalse(@opendir(vfsStream::url('root')));
        assertFalse(@dir(vfsStream::url('root')));
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
     * @test
     */
    public function canNotUnlinkDirectoryWithoutRoot()
    {
        vfsStreamWrapper::register();
        assertFalse(@rmdir(vfsStream::url('foo')));
    }

    /**
     * @test
     */
    public function rmdirCanNotRemoveFiles()
    {
        assertFalse(rmdir($this->baz1URL));
    }

    /**
     * @test
     */
    public function rmdirCanNotRemoveNonExistingDirectory()
    {
        assertFalse(rmdir($this->fooURL . '/another'));
    }

    /**
     * @test
     */
    public function rmdirCanNotRemoveNonEmptyDirectory()
    {
        assertFalse(rmdir($this->fooURL));
    }

    /**
     * @test
     */
    public function rmdirCanRemoveEmptyDirectory()
    {
        vfsStream::newDirectory('empty')->at($this->foo);
        assertTrue(rmdir($this->fooURL . '/empty'));
        assertFalse($this->foo->hasChild('empty'));
    }

    /**
     * @test
     */
    public function rmdirCanRemoveEmptyDirectoryWithDot()
    {
        vfsStream::newDirectory('empty')->at($this->foo);
        assertTrue(rmdir($this->fooURL . '/empty/.'));
        assertFalse($this->foo->hasChild('empty'));
    }

    /**
     * @test
     */
    public function rmdirCanRemoveEmptyRoot()
    {
        $this->foo->removeChild('bar');
        $this->foo->removeChild('baz2');
        assertTrue(rmdir($this->fooURL));
        assertFalse(file_exists($this->fooURL)); // make sure statcache was cleared
        assertNull(vfsStreamWrapper::getRoot());
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function rmdirDirCanNotRemoveDirFromNonWritingDirectory()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(vfsStream::newDirectory('root', 0000));
        vfsStreamWrapper::getRoot()->addChild(vfsStream::newDirectory('nonRemovableFolder'));
        assertFalse(is_writable(vfsStream::url('root')));
        assertFalse(rmdir(vfsStream::url('root/nonRemovableFolder')));
        assertTrue(vfsStreamWrapper::getRoot()->hasChild('nonRemovableFolder'));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_17
     */
    public function issue17()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(vfsStream::newDirectory('root', 0770));
        vfsStreamWrapper::getRoot()
            ->chgrp(vfsStream::GROUP_USER_1)
            ->chown(vfsStream::OWNER_USER_1);
        assertFalse(mkdir(vfsStream::url('root/doesNotWork')));
        assertFalse(vfsStreamWrapper::getRoot()->hasChild('doesNotWork'));
    }

    /**
     * @test
     * @group  bug_19
     */
    public function accessWithDoubleDotReturnsCorrectContent()
    {
        assert(
            file_get_contents(vfsStream::url('foo/bar/../baz2')),
            equals('baz2')
        );
    }

    /**
     * @test
     * @group bug_115
     */
    public function accessWithExcessDoubleDotsReturnsCorrectContent()
    {
        assert(
            file_get_contents(vfsStream::url('foo/../../../../bar/../baz2')),
            equals('baz2')
        );
    }

    /**
     * @test
     * @group bug_115
     */
    public function alwaysResolvesRootDirectoryAsOwnParentWithDoubleDot()
    {
        vfsStreamWrapper::getRoot()->chown(vfsStream::OWNER_USER_1);
        assertTrue(is_dir(vfsStream::url('foo/..')));
        $stat = stat(vfsStream::url('foo/..'));
        assert($stat['uid'], equals(vfsStream::OWNER_USER_1));
    }


    /**
     * @test
     * @since  0.11.0
     * @group  issue_23
     */
    public function unlinkCanNotRemoveNonEmptyDirectory()
    {
        expect(function() { assertFalse(unlink($this->barURL)); })
          ->triggers()
          ->withMessage('unlink(vfs://foo/bar): Operation not permitted');
        assertTrue($this->foo->hasChild('bar'));
        $this->assertFileExists($this->barURL);
    }

    /**
     * @test
     * @since  0.11.0
     * @group  issue_23
     */
    public function unlinkCanNotRemoveEmptyDirectory()
    {
        $url = vfsStream::newDirectory('empty')->at($this->foo)->url();
        expect(function() use ($url) { assertFalse(unlink($url)); })
          ->triggers()
          ->withMessage('unlink(vfs://foo/empty): Operation not permitted');

        assertTrue($this->foo->hasChild('empty'));
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
