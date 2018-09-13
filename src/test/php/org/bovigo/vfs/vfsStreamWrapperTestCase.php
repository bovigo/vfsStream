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
    assertEmptyString,
    assertFalse,
    assertNull,
    assertTrue,
    expect,
    predicate\equals,
    predicate\isExistingDirectory,
    predicate\isExistingFile,
    predicate\isNonExistingDirectory,
    predicate\isNonExistingFile,
    predicate\isSameAs
};
/**
 * Test for org\bovigo\vfs\vfsStreamWrapper.
 */
class vfsStreamWrapperTestCase extends vfsStreamWrapperBaseTestCase
{
    /**
     * ensure that a call to vfsStreamWrapper::register() resets the stream
     *
     * Implemented after a request by David Zülke.
     *
     * @test
     */
    public function resetByRegister()
    {
        vfsStream::setup();
        vfsStreamWrapper::register();
        assertNull(vfsStreamWrapper::getRoot());
    }

    /**
     * @test
     * @since  0.11.0
     */
    public function setRootReturnsRoot()
    {
        vfsStreamWrapper::register();
        $root = vfsStream::newDirectory('root');
        assertThat(vfsStreamWrapper::setRoot($root), isSameAs($root));
    }

    /**
     * @test
     */
    public function filesizeOfDirectoryIsZero()
    {
        assertThat(filesize($this->root->url()), equals(0));
    }

    /**
     * @test
     */
    public function filesizeOfFile()
    {
        assertThat(filesize($this->fileInSubdir->url()), equals(6));
    }

    /**
     * @test
     * @dataProvider  elements
     */
    public function file_existsReturnsTrueForAllExistingFilesAndDirectories($element)
    {
        assertTrue(file_exists($this->$element->url()));
    }

    /**
     * @test
     */
    public function file_existsReturnsFalseForNonExistingFiles()
    {
        assertFalse(file_exists($this->root->url() . '/doesNotExist'));
    }

    /**
     * @test
     */
    public function filemtime()
    {
        $this->root->lastModified(100)
            ->lastAccessed(100)
            ->lastAttributeModified(100);
        assertThat(filemtime($this->root->url()), equals(100));
        assertThat(filemtime($this->root->url() . '/.'), equals(100));
    }

    /**
     * @test
     * @group  issue_23
     */
    public function unlinkRemovesFiles()
    {
        assertTrue(unlink($this->fileInRoot->url()));
        assertFalse(file_exists($this->fileInRoot->url())); // make sure statcache was cleared
        assertFalse($this->root->hasChild('file2'));
    }

    /**
     * @test
     * @group  issue_49
     */
    public function unlinkReturnsFalseWhenFileDoesNotExist()
    {
        assertFalse(@unlink(vfsStream::url('root.blubb2')));
    }

    /**
     * @test
     * @group  issue_49
     */
    public function unlinkReturnsFalseWhenFileDoesNotExistAndFileWithSameNameExistsInRoot()
    {
        vfsStream::setup()->addChild(vfsStream::newFile('foo.blubb'));
        assertFalse(@unlink(vfsStream::url('foo.blubb')));
    }

    /**
     * @test
     */
    public function dirnameReturnsDirectoryPath()
    {
        assertThat(
            dirname($this->fileInSubdir->url()),
            equals($this->subdir->url())
        );
    }

    /**
     * this seems not to be fixable because dirname() does not call the stream wrapper
     *
     * @test
     */
    public function dirnameForNonExistingPathDoesNotWork()
    {
        assertThat(
            dirname(vfsStream::url('doesNotExist')),
            equals('vfs:') // should be '.'
        );
    }

    public function basenames(): array
    {
        return [
            [vfsStream::url('root/subdir'), 'subdir'],
            [vfsStream::url('root/subdir/file1'), 'file1'],
            [vfsStream::url('doesNotExist'), 'doesNotExist']
        ];
    }

    /**
     * @test
     * @dataProvider  basenames
     */
    public function basename($path, $basename)
    {
        assertThat(basename($path), equals($basename));
    }

    /**
     * @test
     * @dataProvider  elements
     */
    public function is_readable($element)
    {
        assertTrue(is_readable($this->$element->url()));
        assertTrue(is_readable($this->$element->url() . '/.'));
    }

    /**
     * @test
     * @dataProvider  elements
     */
    public function isNotReadableWithoutReadPermissions($element)
    {
        $this->$element->chmod(0222);
        assertFalse(is_readable($this->$element->url()));
    }

    /**
     * @test
     */
    public function nonExistingIsNotReadable()
    {
        assertFalse(is_readable(vfsStream::url('doesNotExist')));
    }

    /**
     * @test
     * @dataProvider  elements
     */
    public function is_writable($element)
    {
        assertTrue(is_writable($this->$element->url()));
        assertTrue(is_writable($this->$element->url() . '/.'));
    }

    /**
     * @test
     * @dataProvider  elements
     */
    public function isNotWritableWithoutWritePermissions($element)
    {
        $this->$element->chmod(0444);
        assertFalse(is_writable($this->$element->url()));
    }

    /**
     * @test
     */
    public function nonExistingIsNotWritable()
    {
        assertFalse(is_writable(vfsStream::url('doesNotExist')));
    }

    /**
     * @test
     */
    public function nonExistingIsNotExecutable()
    {
        assertFalse(is_executable(vfsStream::url('doesNotExist')));
    }

    /**
     * @test
     */
    public function isNotExecutableByDefault()
    {
        assertFalse(is_executable($this->fileInSubdir->url()));
    }

    /**
     * @test
     */
    public function isExecutableWithCorrectPermission()
    {
        $this->fileInSubdir->chmod(0766);
        assertTrue(is_executable($this->fileInSubdir->url()));
    }

    /**
     * @test
     */
    public function directoriesAreNeverExecutable()
    {
        $this->root->chmod(0766);
        assertFalse(is_executable($this->root->url()));
        assertFalse(is_executable($this->root->url() . '/.'));
    }

    /**
     * @test
     * @dataProvider  elements
     * @group  permissions
     */
    public function filePermissionsAreReturned($element, $permissions)
    {
        assertThat(decoct(fileperms($this->$element->url())), equals($permissions));
        assertThat(decoct(fileperms($this->$element->url() . '/.')), equals($permissions));
    }

    /**
     * @test
     * @group  permissions
     */
    public function filePermissionsCanBeChanged()
    {
        $this->root->chmod(0755);
        assertThat(decoct(fileperms($this->root->url())), equals(40755));
    }

    /**
     * @test
     * @group  issue_11
     * @group  permissions
     */
    public function chmodModifiesPermissions()
    {
        assertTrue(chmod($this->root->url(), 0755));
        assertThat(decoct(fileperms($this->root->url())), equals(40755));
    }

    /**
     * @test
     * @group  issue_11
     * @group  permissions
     */
    public function chownChangesUser()
    {
        assertTrue(chown($this->root->url(), vfsStream::OWNER_USER_1));
        assertThat(fileowner($this->root->url()), equals(vfsStream::OWNER_USER_1));
        assertThat(fileowner($this->root->url() . '/.'), equals(vfsStream::OWNER_USER_1));
    }

    /**
     * @test
     * @group  issue_11
     * @group  permissions
     */
    public function chgrpChangesGroup()
    {
        assertTrue(chgrp($this->root->url(), vfsStream::GROUP_USER_1));
        assertThat(filegroup($this->root->url()), equals(vfsStream::GROUP_USER_1));
        assertThat(filegroup($this->root->url() . '/.'), equals(vfsStream::GROUP_USER_1));
    }

    public function targets(): array
    {
        return [
            [vfsStream::url('root/subdir'), vfsStream::url('root/baz3')],
            [vfsStream::url('root/subdir/.'), vfsStream::url('root/baz3')],
            [vfsStream::url('root/subdir'), vfsStream::url('root/../baz3/.')]
        ];
    }

    /**
     * @test
     * @dataProvider targets
     * @group  issue_9
     * @since  0.9.0
     */
    public function renameDirectory($source, $target)
    {
        assertTrue(rename($source, $target));
        assertThat($target, isExistingDirectory());
        assertThat($source, isNonExistingDirectory());
    }

    /**
     * @test
     * @author  Benoit Aubuchon
     */
    public function renameDirectoryOverwritingExistingFile()
    {
        // move root/subdir to root/file2
        $oldURL = $this->subdir->url();
        assertTrue(rename($oldURL, $this->fileInRoot->url()));
        assertThat(vfsStream::url('root/file2/file1'), isExistingFile());
        assertThat($oldURL, isNonExistingDirectory());
    }

    /**
     * @test
     */
    public function renameFileIntoFileTriggersWarningAndDoesNotChangeFiles()
    {
        // root/file2 is a file, so it can not be turned into a directory
        $oldURL  = $this->fileInSubdir->url();
        $baz3URL = vfsStream::url('root/file2/baz3');
        expect(function() use ($baz3URL) {
            assertTrue(rename($this->fileInSubdir->url(), $baz3URL));
        })->triggers(E_USER_WARNING)
          ->after($baz3URL, isNonExistingFile())
          ->after($oldURL, isExistingFile());
    }

    /**
     * @test
     * @author  Benoit Aubuchon
     */
    public function moveFileToAnotherDirectoryDirectory()
    {
        // move root/subdir/file1 to root/baz3
        $oldURL  = $this->fileInSubdir->url();
        $baz3URL = vfsStream::url('root/baz3');
        assertTrue(rename($this->fileInSubdir->url(), $baz3URL));
        assertThat($baz3URL, isExistingFile());
        assertThat($oldURL, isNonExistingFile());
    }

    /**
     * @test
     * @author  Benoit Aubuchon
     */
    public function moveFileToAnotherDirectoryDoesNotChangeExistingDirectory()
    {
        // move root/subdir/file1 to root/baz3
        assertTrue(rename($this->fileInSubdir->url(), $this->root->url() . '/baz3'));
        assertThat($this->subdir->url(), isExistingDirectory());
    }

    /**
     * @test
     */
    public function renameNonExistingFileTriggersWarning()
    {
        expect(function() {
            rename(vfsStream::url('doesNotExist'), $this->fileInSubdir->url());
        })->triggers(E_USER_WARNING);
    }
    /**
     * @test
     */
    public function renameIntoNonExistingDirectoryTriggersWarning()
    {
      expect(function() {
          rename($this->fileInSubdir->url(), vfsStream::url('root/doesNotExist/file2'));
      })->triggers(E_USER_WARNING);
    }
    /**
     * @test
     */
    public function statAndFstatReturnSameResult()
    {
        $fp = fopen($this->fileInRoot->url(), 'r');
        assertThat(stat($this->fileInRoot->url()), equals(fstat($fp)));
        fclose($fp);
    }

    /**
     * @test
     */
    public function statReturnsFullDataForFiles()
    {
        $this->fileInRoot->lastModified(400)
            ->lastAccessed(400)
            ->lastAttributeModified(400);
        assertThat(
            stat($this->fileInRoot->url()),
            equals([
                0         => 0,
                1         => 0,
                2         => 0100666,
                3         => 0,
                4         => vfsStream::getCurrentUser(),
                5         => vfsStream::getCurrentGroup(),
                6         => 0,
                7         => 6,
                8         => 400,
                9         => 400,
                10        => 400,
                11        => -1,
                12        => -1,
                'dev'     => 0,
                'ino'     => 0,
                'mode'    => 0100666,
                'nlink'   => 0,
                'uid'     => vfsStream::getCurrentUser(),
                'gid'     => vfsStream::getCurrentGroup(),
                'rdev'    => 0,
                'size'    => 6,
                'atime'   => 400,
                'mtime'   => 400,
                'ctime'   => 400,
                'blksize' => -1,
                'blocks'  => -1
            ])
        );
    }

    /**
     * @test
     */
    public function statReturnsFullDataForDirectories()
    {
        $this->root->lastModified(100)
            ->lastAccessed(100)
            ->lastAttributeModified(100);
        assertThat(
            stat($this->root->url()),
            equals([
                0         => 0,
                1         => 0,
                2         => 0040777,
                3         => 0,
                4         => vfsStream::getCurrentUser(),
                5         => vfsStream::getCurrentGroup(),
                6         => 0,
                7         => 0,
                8         => 100,
                9         => 100,
                10        => 100,
                11        => -1,
                12        => -1,
                'dev'     => 0,
                'ino'     => 0,
                'mode'    => 0040777,
                'nlink'   => 0,
                'uid'     => vfsStream::getCurrentUser(),
                'gid'     => vfsStream::getCurrentGroup(),
                'rdev'    => 0,
                'size'    => 0,
                'atime'   => 100,
                'mtime'   => 100,
                'ctime'   => 100,
                'blksize' => -1,
                'blocks'  => -1
            ])
        );
    }

    /**
     * @test
     */
    public function statReturnsFullDataForDirectoriesWithDot()
    {
      $this->root->lastModified(100)
          ->lastAccessed(100)
          ->lastAttributeModified(100);
      assertThat(
          stat($this->root->url() . '/.'),
          equals([
              0         => 0,
              1         => 0,
              2         => 0040777,
              3         => 0,
              4         => vfsStream::getCurrentUser(),
              5         => vfsStream::getCurrentGroup(),
              6         => 0,
              7         => 0,
              8         => 100,
              9         => 100,
              10        => 100,
              11        => -1,
              12        => -1,
              'dev'     => 0,
              'ino'     => 0,
              'mode'    => 0040777,
              'nlink'   => 0,
              'uid'     => vfsStream::getCurrentUser(),
              'gid'     => vfsStream::getCurrentGroup(),
              'rdev'    => 0,
              'size'    => 0,
              'atime'   => 100,
              'mtime'   => 100,
              'ctime'   => 100,
              'blksize' => -1,
              'blocks'  => -1
          ])
      );
    }

    /**
     * @test
     */
    public function openFileWithoutDirectory()
    {
        vfsStreamWrapper::register();
        expect(function() {
            assertFalse(file_get_contents(vfsStream::url('file.txt')));
        })->triggers(E_WARNING);
    }

    /**
     * @test
     * @group     issue_33
     * @since     1.1.0
     */
    public function truncateRemovesSuperflouosContent()
    {
        $handle = fopen($this->fileInSubdir->url(), "r+");
        assertTrue(ftruncate($handle, 0));
        assertEmptyString(file_get_contents($this->fileInSubdir->url()));
        fclose($handle);
    }

    /**
     * @test
     * @group     issue_33
     * @since     1.1.0
     */
    public function truncateToGreaterSizeAddsZeroBytes()
    {
        $handle = fopen($this->fileInSubdir->url(), "r+");
        assertTrue(ftruncate($handle, 25));
        fclose($handle);
        assertThat(
            file_get_contents($this->fileInSubdir->url()),
            equals("file 1\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0")
        );
    }

    /**
     * @test
     * @group     issue_11
     */
    public function touchCreatesNonExistingFile()
    {
        assertTrue(touch($this->root->url() . '/new.txt'));
        assertTrue($this->root->hasChild('new.txt'));
    }

    /**
     * @test
     * @group     issue_11
     */
    public function touchChangesAccessAndModificationTimeForFile()
    {
        assertTrue(touch($this->fileInSubdir->url(), 303, 313));
        assertThat($this->fileInSubdir->filemtime(), equals(303));
        assertThat($this->fileInSubdir->fileatime(), equals(313));
    }

    /**
     * @test
     * @group     issue_11
     * @group     issue_80
     */
    public function touchChangesTimesToCurrentTimestampWhenNoTimesGiven()
    {
        assertTrue(touch($this->fileInSubdir->url()));
        assertThat($this->fileInSubdir->filemtime(), equals(time(), 1));
        assertThat($this->fileInSubdir->fileatime(), equals(time(), 1));
    }

    /**
     * @test
     * @group     issue_11
     */
    public function touchWithModifiedTimeChangesAccessAndModifiedTime()
    {
        assertTrue(touch($this->fileInSubdir->url(), 303));
        assertThat($this->fileInSubdir->filemtime(), equals(303));
        assertThat($this->fileInSubdir->fileatime(), equals(303));
    }

    /**
     * @test
     * @group     issue_11
     */
    public function touchChangesAccessAndModificationTimeForDirectory()
    {
        assertTrue(touch($this->root->url(), 303, 313));
        assertThat($this->root->filemtime(), equals(303));
        assertThat($this->root->fileatime(), equals(313));
    }

    public function elements(): array
    {
        return [
            ['root', 40777],
            ['subdir', 40777],
            ['fileInSubdir', 100666],
            ['fileInRoot', 100666]
        ];
    }

    /**
     * @test
     * @dataProvider  elements
     * @group  issue_34
     * @since  1.2.0
     */
    public function pathesAreCorrectlySet($element)
    {
        assertThat($this->$element->path(), equals(vfsStream::path($this->$element->url())));
    }

    /**
     * @test
     * @group  issue_34
     * @since  1.2.0
     */
    public function pathIsUpdatedAfterMove()
    {
        $baz3URL = vfsStream::url('root/baz3');
        // move root/subdir/file1 to root/baz3
        assertTrue(rename($this->fileInSubdir->url(), $baz3URL));
        assertThat($this->fileInSubdir->path(), equals(vfsStream::path($baz3URL)));
    }

    /**
     * @test
     * @group  issue_34
     * @since  1.2.0
     */
    public function urlIsUpdatedAfterMove()
    {
        $baz3URL = vfsStream::url('root/baz3');
        // move root/subdir/file1 to root/baz3
        assertTrue(rename($this->fileInSubdir->url(), $baz3URL));
        assertThat($this->fileInSubdir->url(), equals($baz3URL));
    }
}
