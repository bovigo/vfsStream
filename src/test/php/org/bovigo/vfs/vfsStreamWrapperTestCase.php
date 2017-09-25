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
use function bovigo\assert\predicate\equals;
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
        $this->assertSame($this->root, vfsStreamWrapper::getRoot());
        vfsStreamWrapper::register();
        $this->assertNull(vfsStreamWrapper::getRoot());
    }

    /**
     * @test
     * @since  0.11.0
     */
    public function setRootReturnsRoot()
    {
        vfsStreamWrapper::register();
        $root = vfsStream::newDirectory('root');
        $this->assertSame($root, vfsStreamWrapper::setRoot($root));
    }

    /**
     * assure that filesize is returned correct
     *
     * @test
     */
    public function filesize()
    {
        $this->assertEquals(0, filesize($this->root->url()));
        $this->assertEquals(0, filesize($this->root->url() . '/.'));
        $this->assertEquals(0, filesize($this->subdir->url()));
        $this->assertEquals(0, filesize($this->subdir->url() . '/.'));
        $this->assertEquals(6, filesize($this->fileInRoot->url()));
        $this->assertEquals(6, filesize($this->fileInSubdir->url()));
    }

    /**
     * assert that file_exists() delivers correct result
     *
     * @test
     */
    public function file_exists()
    {
        $this->assertTrue(file_exists($this->root->url()));
        $this->assertTrue(file_exists($this->root->url() . '/.'));
        $this->assertTrue(file_exists($this->subdir->url()));
        $this->assertTrue(file_exists($this->subdir->url() . '/.'));
        $this->assertTrue(file_exists($this->fileInSubdir->url()));
        $this->assertTrue(file_exists($this->fileInRoot->url()));
        $this->assertFalse(file_exists($this->root->url() . '/another'));
        $this->assertFalse(file_exists(vfsStream::url('another')));
    }

    /**
     * @test
     */
    public function filemtime()
    {
        $this->root->lastModified(100)
            ->lastAccessed(100)
            ->lastAttributeModified(100);
        assert(filemtime($this->root->url()), equals(100));
        assert(filemtime($this->root->url() . '/.'), equals(100));
    }

    /**
     * @test
     * @group  issue_23
     */
    public function unlinkRemovesFilesOnly()
    {
        $this->assertTrue(unlink($this->fileInRoot->url()));
        $this->assertFalse(file_exists($this->fileInRoot->url())); // make sure statcache was cleared
        $this->assertEquals(array($this->subdir), $this->root->getChildren());
        $this->assertFalse(@unlink($this->root->url() . '/another'));
        $this->assertFalse(@unlink(vfsStream::url('another')));
        $this->assertEquals(array($this->subdir), $this->root->getChildren());
    }

    /**
     * @test
     * @group  issue_49
     */
    public function unlinkReturnsFalseWhenFileDoesNotExist()
    {
        $this->assertFalse(@unlink(vfsStream::url('root.blubb2')));
    }

    /**
     * @test
     * @group  issue_49
     */
    public function unlinkReturnsFalseWhenFileDoesNotExistAndFileWithSameNameExistsInRoot()
    {
        vfsStream::setup()->addChild(vfsStream::newFile('foo.blubb'));
        $this->assertFalse(@unlink(vfsStream::url('foo.blubb')));
    }

    /**
     * assert dirname() returns correct directory name
     *
     * @test
     */
    public function dirname()
    {
        $this->assertEquals($this->root->url(), dirname($this->subdir->url()));
        $this->assertEquals($this->subdir->url(), dirname($this->fileInSubdir->url()));
        # returns "vfs:" instead of "."
        # however this seems not to be fixable because dirname() does not
        # call the stream wrapper
        #$this->assertEquals(dirname(vfsStream::url('doesNotExist')), '.');
    }

    /**
     * assert basename() returns correct file name
     *
     * @test
     */
    public function basename()
    {
        $this->assertEquals('subdir', basename($this->subdir->url()));
        $this->assertEquals('file1', basename($this->fileInSubdir->url()));
        $this->assertEquals('doesNotExist', basename(vfsStream::url('doesNotExist')));
    }

    /**
     * assert is_readable() works correct
     *
     * @test
     */
    public function is_readable()
    {
        $this->assertTrue(is_readable($this->root->url()));
        $this->assertTrue(is_readable($this->root->url() . '/.'));
        $this->assertTrue(is_readable($this->subdir->url()));
        $this->assertTrue(is_readable($this->subdir->url() . '/.'));
        $this->assertTrue(is_readable($this->fileInSubdir->url()));
        $this->assertTrue(is_readable($this->fileInRoot->url()));
        $this->assertFalse(is_readable($this->root->url() . '/another'));
        $this->assertFalse(is_readable(vfsStream::url('another')));

        $this->root->chmod(0222);
        $this->assertFalse(is_readable($this->root->url()));

        $this->fileInSubdir->chmod(0222);
        $this->assertFalse(is_readable($this->fileInSubdir->url()));
    }

    /**
     * assert is_writable() works correct
     *
     * @test
     */
    public function is_writable()
    {
        $this->assertTrue(is_writable($this->root->url()));
        $this->assertTrue(is_writable($this->root->url() . '/.'));
        $this->assertTrue(is_writable($this->subdir->url()));
        $this->assertTrue(is_writable($this->subdir->url() . '/.'));
        $this->assertTrue(is_writable($this->fileInSubdir->url()));
        $this->assertTrue(is_writable($this->fileInRoot->url()));
        $this->assertFalse(is_writable($this->root->url() . '/another'));
        $this->assertFalse(is_writable(vfsStream::url('another')));

        $this->root->chmod(0444);
        $this->assertFalse(is_writable($this->root->url()));

        $this->fileInSubdir->chmod(0444);
        $this->assertFalse(is_writable($this->fileInSubdir->url()));
    }

    /**
     * assert is_executable() works correct
     *
     * @test
     */
    public function is_executable()
    {
        $this->assertFalse(is_executable($this->fileInSubdir->url()));
        $this->fileInSubdir->chmod(0766);
        $this->assertTrue(is_executable($this->fileInSubdir->url()));
        $this->assertFalse(is_executable($this->fileInRoot->url()));
    }

    /**
     * assert is_executable() works correct
     *
     * @test
     */
    public function directoriesAndNonExistingFilesAreNeverExecutable()
    {
        $this->assertFalse(is_executable($this->root->url()));
        $this->assertFalse(is_executable($this->root->url() . '/.'));
        $this->assertFalse(is_executable($this->subdir->url()));
        $this->assertFalse(is_executable($this->subdir->url() . '/.'));
        $this->assertFalse(is_executable($this->root->url() . '/another'));
        $this->assertFalse(is_executable(vfsStream::url('another')));
    }

    /**
     * file permissions
     *
     * @test
     * @group  permissions
     */
    public function chmod()
    {
        $this->assertEquals(40777, decoct(fileperms($this->root->url())));
        $this->assertEquals(40777, decoct(fileperms($this->root->url() . '/.')));
        $this->assertEquals(40777, decoct(fileperms($this->subdir->url())));
        $this->assertEquals(40777, decoct(fileperms($this->subdir->url() . '/.')));
        $this->assertEquals(100666, decoct(fileperms($this->fileInSubdir->url())));
        $this->assertEquals(100666, decoct(fileperms($this->fileInRoot->url())));

        $this->root->chmod(0755);
        $this->subdir->chmod(0700);
        $this->fileInSubdir->chmod(0644);
        $this->fileInRoot->chmod(0600);
        $this->assertEquals(40755, decoct(fileperms($this->root->url())));
        $this->assertEquals(40755, decoct(fileperms($this->root->url() . '/.')));
        $this->assertEquals(40700, decoct(fileperms($this->subdir->url())));
        $this->assertEquals(40700, decoct(fileperms($this->subdir->url() . '/.')));
        $this->assertEquals(100644, decoct(fileperms($this->fileInSubdir->url())));
        $this->assertEquals(100600, decoct(fileperms($this->fileInRoot->url())));
    }

    /**
     * @test
     * @group  issue_11
     * @group  permissions
     */
    public function chmodModifiesPermissions()
    {
        $this->assertTrue(chmod($this->root->url(), 0755));
        $this->assertTrue(chmod($this->subdir->url(), 0711));
        $this->assertTrue(chmod($this->fileInSubdir->url(), 0644));
        $this->assertTrue(chmod($this->fileInRoot->url(), 0664));
        $this->assertEquals(40755, decoct(fileperms($this->root->url())));
        $this->assertEquals(40711, decoct(fileperms($this->subdir->url())));
        $this->assertEquals(100644, decoct(fileperms($this->fileInSubdir->url())));
        $this->assertEquals(100664, decoct(fileperms($this->fileInRoot->url())));
    }

    /**
     * @test
     * @group  permissions
     */
    public function fileownerIsCurrentUserByDefault()
    {
        $this->assertEquals(vfsStream::getCurrentUser(), fileowner($this->root->url()));
        $this->assertEquals(vfsStream::getCurrentUser(), fileowner($this->root->url() . '/.'));
        $this->assertEquals(vfsStream::getCurrentUser(), fileowner($this->subdir->url()));
        $this->assertEquals(vfsStream::getCurrentUser(), fileowner($this->subdir->url() . '/.'));
        $this->assertEquals(vfsStream::getCurrentUser(), fileowner($this->fileInSubdir->url()));
        $this->assertEquals(vfsStream::getCurrentUser(), fileowner($this->fileInRoot->url()));
    }

    /**
     * @test
     * @group  issue_11
     * @group  permissions
     */
    public function chownChangesUser()
    {
        chown($this->root->url(), vfsStream::OWNER_USER_1);
        chown($this->subdir->url(), vfsStream::OWNER_USER_1);
        chown($this->fileInSubdir->url(), vfsStream::OWNER_USER_2);
        chown($this->fileInRoot->url(), vfsStream::OWNER_USER_2);
        $this->assertEquals(vfsStream::OWNER_USER_1, fileowner($this->root->url()));
        $this->assertEquals(vfsStream::OWNER_USER_1, fileowner($this->root->url() . '/.'));
        $this->assertEquals(vfsStream::OWNER_USER_1, fileowner($this->subdir->url()));
        $this->assertEquals(vfsStream::OWNER_USER_1, fileowner($this->subdir->url() . '/.'));
        $this->assertEquals(vfsStream::OWNER_USER_2, fileowner($this->fileInSubdir->url()));
        $this->assertEquals(vfsStream::OWNER_USER_2, fileowner($this->fileInRoot->url()));
    }

    /**
     * @test
     * @group  issue_11
     * @group  permissions
     */
    public function groupIsCurrentGroupByDefault()
    {
        $this->assertEquals(vfsStream::getCurrentGroup(), filegroup($this->root->url()));
        $this->assertEquals(vfsStream::getCurrentGroup(), filegroup($this->root->url() . '/.'));
        $this->assertEquals(vfsStream::getCurrentGroup(), filegroup($this->subdir->url()));
        $this->assertEquals(vfsStream::getCurrentGroup(), filegroup($this->subdir->url() . '/.'));
        $this->assertEquals(vfsStream::getCurrentGroup(), filegroup($this->fileInSubdir->url()));
        $this->assertEquals(vfsStream::getCurrentGroup(), filegroup($this->fileInRoot->url()));
    }

    /**
     * @test
     * @group  issue_11
     * @group  permissions
     */
    public function chgrp()
    {
        chgrp($this->root->url(), vfsStream::GROUP_USER_1);
        chgrp($this->subdir->url(), vfsStream::GROUP_USER_1);
        chgrp($this->fileInSubdir->url(), vfsStream::GROUP_USER_2);
        chgrp($this->fileInRoot->url(), vfsStream::GROUP_USER_2);
        $this->assertEquals(vfsStream::GROUP_USER_1, filegroup($this->root->url()));
        $this->assertEquals(vfsStream::GROUP_USER_1, filegroup($this->root->url() . '/.'));
        $this->assertEquals(vfsStream::GROUP_USER_1, filegroup($this->subdir->url()));
        $this->assertEquals(vfsStream::GROUP_USER_1, filegroup($this->subdir->url() . '/.'));
        $this->assertEquals(vfsStream::GROUP_USER_2, filegroup($this->fileInSubdir->url()));
        $this->assertEquals(vfsStream::GROUP_USER_2, filegroup($this->fileInRoot->url()));
    }

    /**
     * @test
     * @author  Benoit Aubuchon
     */
    public function renameDirectory()
    {
        // move root/subdir to root/baz3
        $oldURL  = $this->subdir->url();
        $baz3URL = vfsStream::url('root/baz3');
        $this->assertTrue(rename($this->subdir->url(), $baz3URL));
        $this->assertFileExists($baz3URL);
        $this->assertFileNotExists($oldURL);
    }

    /**
     * @test
     */
    public function renameDirectoryWithDots()
    {
        // move root/subdir to root/baz3
        $oldURL  = $this->subdir->url();
        $baz3URL = vfsStream::url('root/baz3');
        $this->assertTrue(rename($this->subdir->url() . '/.', $baz3URL));
        $this->assertFileExists($baz3URL);
        $this->assertFileNotExists($oldURL);
    }

    /**
     * @test
     * @group  issue_9
     * @since  0.9.0
     */
    public function renameDirectoryWithDotsInTarget()
    {
        // move root/subdir to root/baz3
        $oldURL  = $this->subdir->url();
        $baz3URL = vfsStream::url('root/../baz3/.');
        $this->assertTrue(rename($this->subdir->url() . '/.', $baz3URL));
        $this->assertFileExists($baz3URL);
        $this->assertFileNotExists($oldURL);
    }

    /**
     * @test
     * @author  Benoit Aubuchon
     */
    public function renameDirectoryOverwritingExistingFile()
    {
        // move root/subdir to root/file2
        $oldURL = $this->subdir->url();
        $this->assertTrue(rename($oldURL, $this->fileInRoot->url()));
        $this->assertFileExists(vfsStream::url('root/file2/file1'));
        $this->assertFileNotExists($oldURL);
    }

    /**
     * @test
     * @expectedException  \PHPUnit\Framework\Error\Error
     */
    public function renameFileIntoFile()
    {
        // root/file2 is a file, so it can not be turned into a directory
        $oldURL  = $this->fileInSubdir->url();
        $baz3URL = vfsStream::url('root/file2/baz3');
        $this->assertTrue(rename($this->fileInSubdir->url(), $baz3URL));
        $this->assertFileExists($baz3URL);
        $this->assertFileNotExists($oldURL);
    }

    /**
     * @test
     * @author  Benoit Aubuchon
     */
    public function renameFileToDirectory()
    {
        // move root/subdir/file1 to root/baz3
        $oldURL  = $this->fileInSubdir->url();
        $baz3URL = vfsStream::url('root/baz3');
        $this->assertTrue(rename($this->fileInSubdir->url(), $baz3URL));
        $this->assertFileExists($this->subdir->url());
        $this->assertFileExists($baz3URL);
        $this->assertFileNotExists($oldURL);
    }

    /**
     * assert that trying to rename from a non existing file trigger a warning
     *
     * @expectedException \PHPUnit\Framework\Error\Error
     * @test
     */
    public function renameOnSourceFileNotFound()
    {
        rename(vfsStream::url('notfound'), $this->fileInSubdir->url());
    }
    /**
     * assert that trying to rename to a directory that is not found trigger a warning

     * @expectedException \PHPUnit\Framework\Error\Error
     * @test
     */
    public function renameOnDestinationDirectoryFileNotFound()
    {
        rename($this->fileInSubdir->url(), vfsStream::url('root/notfound/file2'));
    }
    /**
     * stat() and fstat() should return the same result
     *
     * @test
     */
    public function statAndFstatReturnSameResult()
    {
        $fp = fopen($this->fileInRoot->url(), 'r');
        $this->assertEquals(stat($this->fileInRoot->url()),
                            fstat($fp)
        );
        fclose($fp);
    }

    /**
     * stat() returns full data
     *
     * @test
     */
    public function statReturnsFullDataForFiles()
    {
        $this->fileInRoot->lastModified(400)
            ->lastAccessed(400)
            ->lastAttributeModified(400);
        assert(
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
        assert(
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
      $this->assertEquals(array(0         => 0,
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
                            ),
                            stat($this->root->url() . '/.')
        );
    }

    /**
     * @test
     * @expectedException \PHPUnit\Framework\Error\Error
     */
    public function openFileWithoutDirectory()
    {
        vfsStreamWrapper::register();
        $this->assertFalse(file_get_contents(vfsStream::url('file.txt')));
    }

    /**
     * @test
     * @group     issue_33
     * @since     1.1.0
     */
    public function truncateRemovesSuperflouosContent()
    {
        $handle = fopen($this->fileInSubdir->url(), "r+");
        $this->assertTrue(ftruncate($handle, 0));
        $this->assertEquals(0, filesize($this->fileInSubdir->url()));
        $this->assertEquals('', file_get_contents($this->fileInSubdir->url()));
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
        $this->assertTrue(ftruncate($handle, 25));
        $this->assertEquals(25, filesize($this->fileInSubdir->url()));
        $this->assertEquals("file 1\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0",
                            file_get_contents($this->fileInSubdir->url()));
        fclose($handle);
    }

    /**
     * @test
     * @group     issue_11
     */
    public function touchCreatesNonExistingFile()
    {
        $this->assertTrue(touch($this->root->url() . '/new.txt'));
        $this->assertTrue($this->root->hasChild('new.txt'));
    }

    /**
     * @test
     * @group     issue_11
     */
    public function touchChangesAccessAndModificationTimeForFile()
    {
        $this->assertTrue(touch($this->fileInSubdir->url(), 303, 313));
        $this->assertEquals(303, $this->fileInSubdir->filemtime());
        $this->assertEquals(313, $this->fileInSubdir->fileatime());
    }

    /**
     * @test
     * @group     issue_11
     * @group     issue_80
     */
    public function touchChangesTimesToCurrentTimestampWhenNoTimesGiven()
    {
        $this->assertTrue(touch($this->fileInSubdir->url()));
        $this->assertEquals(time(), $this->fileInSubdir->filemtime(), '', 1);
        $this->assertEquals(time(), $this->fileInSubdir->fileatime(), '', 1);
    }

    /**
     * @test
     * @group     issue_11
     */
    public function touchWithModifiedTimeChangesAccessAndModifiedTime()
    {
        $this->assertTrue(touch($this->fileInSubdir->url(), 303));
        $this->assertEquals(303, $this->fileInSubdir->filemtime());
        $this->assertEquals(303, $this->fileInSubdir->fileatime());
    }

    /**
     * @test
     * @group     issue_11
     */
    public function touchChangesAccessAndModificationTimeForDirectory()
    {
        $this->assertTrue(touch($this->root->url(), 303, 313));
        $this->assertEquals(303, $this->root->filemtime());
        $this->assertEquals(313, $this->root->fileatime());
    }

    /**
     * @test
     * @group  issue_34
     * @since  1.2.0
     */
    public function pathesAreCorrectlySet()
    {
        $this->assertEquals(vfsStream::path($this->root->url()), $this->root->path());
        $this->assertEquals(vfsStream::path($this->subdir->url()), $this->subdir->path());
        $this->assertEquals(vfsStream::path($this->fileInSubdir->url()), $this->fileInSubdir->path());
        $this->assertEquals(vfsStream::path($this->fileInRoot->url()), $this->fileInRoot->path());
    }

    /**
     * @test
     * @group  issue_34
     * @since  1.2.0
     */
    public function urlsAreCorrectlySet()
    {
        $this->assertEquals($this->root->url(), $this->root->url());
        $this->assertEquals($this->subdir->url(), $this->subdir->url());
        $this->assertEquals($this->fileInSubdir->url(), $this->fileInSubdir->url());
        $this->assertEquals($this->fileInRoot->url(), $this->fileInRoot->url());
    }

    /**
     * @test
     * @group  issue_34
     * @since  1.2.0
     */
    public function pathIsUpdatedAfterMove()
    {
        // move root/subdir/file1 to root/baz3
        $baz3URL = vfsStream::url('root/baz3');
        $this->assertTrue(rename($this->fileInSubdir->url(), $baz3URL));
        $this->assertEquals(vfsStream::path($baz3URL), $this->fileInSubdir->path());
    }

    /**
     * @test
     * @group  issue_34
     * @since  1.2.0
     */
    public function urlIsUpdatedAfterMove()
    {
        // move root/subdir/file1 to root/baz3
        $baz3URL = vfsStream::url('root/baz3');
        $this->assertTrue(rename($this->fileInSubdir->url(), $baz3URL));
        $this->assertEquals($baz3URL, $this->fileInSubdir->url());
    }
}
