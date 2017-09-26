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
use function bovigo\assert\assertEmptyString;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
/**
 * Test for org\bovigo\vfs\vfsStreamWrapper.
 */
class vfsStreamWrapperFileTestCase extends vfsStreamWrapperBaseTestCase
{
    /**
     * @test
     */
    public function file_get_contentsReturnsFileContents()
    {
        assert(file_get_contents($this->fileInRoot->url()), equals('file 2'));
    }

    /**
     * @test
     */
    public function file_get_contentsReturnsFalseForDirectories()
    {
        assertFalse(@file_get_contents($this->subdir->url()));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function file_get_contentsReturnsEmptyStringForNonReadableFile()
    {
        vfsStream::newFile('new.txt', 0000)->at($this->root)->withContent('content');
        assertEmptyString(@file_get_contents(vfsStream::url('root/new.txt')));
    }

    /**
     * @test
     */
    public function file_put_contentsReturnsAmountOfWrittenBytes()
    {
        assert(
            file_put_contents($this->fileInRoot->url(), 'baz is not bar'),
            equals(14)
        );
    }

    /**
     * @test
     */
    public function file_put_contentsExistingFile()
    {
        file_put_contents($this->fileInRoot->url(), 'baz is not bar');
        assert($this->fileInRoot->getContent(), equals('baz is not bar'));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function file_put_contentsExistingFileNonWritableDirectory()
    {
        $this->root->chmod(0000);
        file_put_contents($this->fileInRoot->url(), 'This does work.');
        assert($this->fileInRoot->getContent(), equals('This does work.'));

    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function file_put_contentsExistingNonWritableFile()
    {
        $this->fileInRoot->chmod(0400);
        assertFalse(@file_put_contents($this->fileInRoot->url(), 'This does not work.'));
        assert($this->fileInRoot->getContent(), equals('file 2'));
    }

    /**
     * assert that file_put_contents() delivers correct file contents
     *
     * @test
     */
    public function file_put_contentsNonExistingFile()
    {
        file_put_contents($this->root->url() . '/baznot.bar', 'baz is not bar');
        assert($this->root->getChild('baznot.bar')->getContent(), equals('baz is not bar'));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function file_put_contentsNonExistingFileNonWritableDirectory()
    {
        $this->root->chmod(0000);
        assertFalse(@file_put_contents(vfsStream::url('root/new.txt'), 'This does not work.'));
    }

    /**
     * @test
     */
    public function filePointerKnowsPositionInFile()
    {
        $fp = fopen($this->fileInSubdir->url(), 'r');
        assert(ftell($fp), equals(0));
        fclose($fp);
    }

    public function seekArgs(): array
    {
        return [
            [2, null, 2],
            [1, SEEK_CUR, 1],
            [1, SEEK_END, 7],
        ];
    }

    /**
     * @test
     * @dataProvider  seekArgs
     */
    public function canSeekInFile($where, $whence, $pos)
    {
        $fp = fopen($this->fileInSubdir->url(), 'r');
        assert(fseek($fp, $where, $whence), equals(0));
        assert(ftell($fp), equals($pos));
        fclose($fp);
    }

    /**
     * @test
     */
    public function recognizesEof()
    {
        $fp = fopen($this->fileInSubdir->url(), 'r');
        fseek($fp, 1, SEEK_END);
        assertTrue(feof($fp));
        fclose($fp);
    }

    /**
     * @test
     */
    public function readsFromSeekedPosition()
    {
        $fp = fopen($this->fileInSubdir->url(), 'r');
        fseek($fp, 2);
        assert(fread($fp, 1), equals('l'));
        fclose($fp);
    }

    /**
     * @test
     */
    public function readingMovesPosition()
    {
        $fp = fopen($this->fileInSubdir->url(), 'r');
        fread($fp, 8092);
        assert(ftell($fp), equals(6));
        fclose($fp);
    }

    /**
     * @test
     */
    public function is_fileReturnsFalseForDirectory()
    {
        assertFalse(is_file($this->root->url()));
    }

    /**
     * @test
     */
    public function is_fileReturnsTrueForFile()
    {
        assertTrue(is_file($this->fileInSubdir->url()));
    }

    /**
     * @test
     */
    public function is_fileReturnsFalseForNonExisting()
    {
        assertFalse(is_file($this->root->url() . '/doesNotExist'));
    }

    /**
     * @test
     * @group  issue7
     * @group  issue13
     */
    public function issue13CanNotOverwriteFiles()
    {
        $vfsFile = vfsStream::url('root/overwrite.txt');
        file_put_contents($vfsFile, 'test');
        file_put_contents($vfsFile, 'd');
        assert(file_get_contents($vfsFile), equals('d'));
    }

    /**
     * @test
     * @group  issue7
     * @group  issue13
     */
    public function appendContentIfOpenedWithModeA()
    {
        $vfsFile = vfsStream::url('root/overwrite.txt');
        file_put_contents($vfsFile, 'test');
        $fp = fopen($vfsFile, 'ab');
        fwrite($fp, 'd');
        fclose($fp);
        assert(file_get_contents($vfsFile), equals('testd'));
    }

    /**
     * @test
     * @group  issue7
     * @group  issue13
     */
    public function canOverwriteNonExistingFileWithModeX()
    {
        $vfsFile = vfsStream::url('root/overwrite.txt');
        $fp = fopen($vfsFile, 'xb');
        fwrite($fp, 'test');
        fclose($fp);
        assert(file_get_contents($vfsFile), equals('test'));
    }

    /**
     * @test
     * @group  issue7
     * @group  issue13
     */
    public function canNotOverwriteExistingFileWithModeX()
    {
        $vfsFile = vfsStream::url('root/overwrite.txt');
        file_put_contents($vfsFile, 'test');
        assertFalse(@fopen($vfsFile, 'xb'));
        assert(file_get_contents($vfsFile), equals('test'));
    }

    /**
     * @test
     * @group  issue7
     * @group  issue13
     */
    public function canNotOpenNonExistingFileReadonly()
    {
        assertFalse(@fopen(vfsStream::url('root/doesNotExist.txt'), 'rb'));
    }

    /**
     * @test
     * @group  issue7
     * @group  issue13
     */
    public function canNotOpenNonExistingFileReadAndWrite()
    {
        assertFalse(@fopen(vfsStream::url('root/doesNotExist.txt'), 'rb+'));
    }

    /**
     * @test
     * @group  issue7
     * @group  issue13
     */
    public function canNotOpenWithIllegalMode()
    {
        assertFalse(@fopen($this->fileInRoot->url(), 'invalid'));
    }

    /**
     * @test
     * @group  issue7
     * @group  issue13
     */
    public function canNotWriteToReadOnlyFile()
    {
        $fp = fopen($this->fileInRoot->url(), 'rb');
        assert(fread($fp, 4096), equals('file 2'));
        assert(fwrite($fp, 'foo'), equals(0));
        fclose($fp);
        assert($this->fileInRoot->getContent(), equals('file 2'));
    }

    /**
     * @test
     * @group  issue7
     * @group  issue13
     */
    public function canNotReadFromWriteOnlyFileWithModeW()
    {
        $fp = fopen($this->fileInRoot->url(), 'wb');
        assertEmptyString(fread($fp, 4096));
        assert(fwrite($fp, 'foo'), equals(3));
        fseek($fp, 0);
        assertEmptyString(fread($fp, 4096));
        fclose($fp);
        assert($this->fileInRoot->getContent(), equals('foo'));
    }

    /**
     * @test
     * @group  issue7
     * @group  issue13
     */
    public function canNotReadFromWriteOnlyFileWithModeA()
    {
        $fp = fopen($this->fileInRoot->url(), 'ab');
        assertEmptyString(fread($fp, 4096));
        assert(fwrite($fp, 'foo'), equals(3));
        fseek($fp, 0);
        assertEmptyString(fread($fp, 4096));
        fclose($fp);
        assert($this->fileInRoot->getContent(), equals('file 2foo'));
    }

    /**
     * @test
     * @group  issue7
     * @group  issue13
     */
    public function canNotReadFromWriteOnlyFileWithModeX()
    {
        $vfsFile = vfsStream::url('root/modeXtest.txt');
        $fp = fopen($vfsFile, 'xb');
        assertEmptyString(fread($fp, 4096));
        assert(fwrite($fp, 'foo'), equals(3));
        fseek($fp, 0);
        assertEmptyString(fread($fp, 4096));
        fclose($fp);
        assert(file_get_contents($vfsFile), equals('foo'));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function canNotRemoveFileFromDirectoryWithoutWritePermissions()
    {
        $this->root->chmod(0000);
        assertFalse(@unlink($this->fileInRoot->url()));
        assertTrue($this->root->hasChild('file2'));
    }

    /**
     * @test
     * @group  issue_30
     */
    public function truncatesFileWhenOpenedWithModeW()
    {
        $vfsFile = vfsStream::url('root/overwrite.txt');
        file_put_contents($vfsFile, 'test');
        $fp = fopen($vfsFile, 'wb');
        assertEmptyString(file_get_contents($vfsFile));
        fclose($fp);
    }

    /**
     * @test
     * @group  issue_30
     */
    public function createsNonExistingFileWhenOpenedWithModeC()
    {
        $vfsFile = vfsStream::url('root/tobecreated.txt');
        $fp = fopen($vfsFile, 'cb');
        fwrite($fp, 'some content');
        assertTrue($this->root->hasChild('tobecreated.txt'));
        fclose($fp);
        assert(file_get_contents($vfsFile), equals('some content'));
    }

    /**
     * @test
     * @group  issue_30
     */
    public function createsNonExistingFileWhenOpenedWithModeCplus()
    {
        $vfsFile = vfsStream::url('root/tobecreated.txt');
        $fp = fopen($vfsFile, 'cb+');
        fwrite($fp, 'some content');
        assertTrue($this->root->hasChild('tobecreated.txt'));
        fclose($fp);
        assert(file_get_contents($vfsFile), equals('some content'));
    }

    /**
     * @test
     * @group  issue_30
     */
    public function doesNotTruncateFileWhenOpenedWithModeC()
    {
        $vfsFile = vfsStream::url('root/overwrite.txt');
        file_put_contents($vfsFile, 'test');
        $fp = fopen($vfsFile, 'cb');
        fclose($fp);
        assert(file_get_contents($vfsFile), equals('test'));
    }

    /**
     * @test
     * @group  issue_30
     */
    public function setsPointerToStartWhenOpenedWithModeC()
    {
        $vfsFile = vfsStream::url('root/overwrite.txt');
        file_put_contents($vfsFile, 'test');
        $fp = fopen($vfsFile, 'cb');
        assert(ftell($fp), equals(0));
        fclose($fp);
    }

    /**
     * @test
     * @group  issue_30
     */
    public function doesNotTruncateFileWhenOpenedWithModeCplus()
    {
        $vfsFile = vfsStream::url('root/overwrite.txt');
        file_put_contents($vfsFile, 'test');
        $fp = fopen($vfsFile, 'cb+');
        fclose($fp);
        assert(file_get_contents($vfsFile), equals('test'));
    }

    /**
     * @test
     * @group  issue_30
     */
    public function setsPointerToStartWhenOpenedWithModeCplus()
    {
        $vfsFile = vfsStream::url('root/overwrite.txt');
        file_put_contents($vfsFile, 'test');
        $fp = fopen($vfsFile, 'cb+');
        assert(ftell($fp), equals(0));
        fclose($fp);
    }

    /**
     * @test
     */
    public function cannotOpenExistingNonwritableFileWithModeA()
    {
        $this->fileInSubdir->chmod(0400);
        assertFalse(@fopen($this->fileInSubdir->url(), 'a'));
    }

    /**
     * @test
     */
    public function cannotOpenExistingNonwritableFileWithModeW()
    {
        $this->fileInSubdir->chmod(0400);
        assertFalse(@fopen($this->fileInSubdir->url(), 'w'));
    }

    /**
     * @test
     */
    public function cannotOpenNonReadableFileWithModeR()
    {
        $this->fileInSubdir->chmod(0000);
        assertFalse(@fopen($this->fileInSubdir->url(), 'r'));
    }

    /**
     * @test
     */
    public function cannotRenameToNonWritableDir()
    {
        $this->subdir->chmod(0000);
        assertFalse(@rename($this->fileInRoot->url(), vfsStream::url('root/bar/baz3')));
    }

    /**
     * @test
     * @group permissions
     * @group issue_38
     */
    // public function cannotReadFileFromNonReadableDir()
    // {
    //     $this->markTestSkipped('Ignored for now, see https://github.com/mikey179/vfsStream/issues/38');
    //     $this->subdir->chmod(0000);
    //     assertFalse(@file_get_contents($this->fileInSubdir->url()));
    // }
}
