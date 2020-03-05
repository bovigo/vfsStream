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
use bovigo\vfs\StreamWrapper;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
use function fclose;
use function file_get_contents;
use function file_put_contents;
use function fileatime;
use function filectime;
use function filemtime;
use function fopen;
use function fread;
use function fwrite;
use function rename;
use function sleep;
use function time;
use function unlink;

/**
 * Test for bovigo\vfs\StreamWrapper.
 *
 * @since  0.9.0
 */
class vfsStreamWrapperFileTimesTestCase extends \BC_PHPUnit_Framework_TestCase
{
    /**
     * URL of foo.txt file
     *
     * @var  string
     */
    protected $fooUrl;
    /**
     * URL of bar directory
     *
     * @var  string
     */
    protected $barUrl;
    /**
     * URL of baz.txt file
     *
     * @var  string
     */
    protected $bazUrl;

    /**
     * set up test environment
     */
    public function setUp()
    {
        vfsStream::setup()
                 ->lastModified(50)
                 ->lastAccessed(50)
                 ->lastAttributeModified(50);
        $this->fooUrl = vfsStream::url('root/foo.txt');
        $this->barUrl = vfsStream::url('root/bar');
        $this->bazUrl = vfsStream::url('root/bar/baz.txt');
    }

    /**
     * helper assertion for the tests
     *
     * @param  string            $url      url to check
     * @param  vfsStreamContent  $content  content to compare
     */
    protected function assertFileTimesEqualStreamTimes($url, vfsStreamContent $content)
    {
        $this->assertEquals(filemtime($url), $content->filemtime());
        $this->assertEquals(fileatime($url), $content->fileatime());
        $this->assertEquals(filectime($url), $content->filectime());
    }

    /**
     * @test
     * @group  issue_7
     * @group  issue_26
     */
    public function openFileChangesAttributeTimeOnly()
    {
        $file = vfsStream::newFile('foo.txt')
                         ->withContent('test')
                         ->at(StreamWrapper::getRoot())
                         ->lastModified(100)
                         ->lastAccessed(100)
                         ->lastAttributeModified(100);
        fclose(fopen($this->fooUrl, 'rb'));
        $this->assertGreaterThan(time() - 2, fileatime($this->fooUrl));
        $this->assertLessThanOrEqual(time(), fileatime($this->fooUrl));
        $this->assertLessThanOrEqual(100, filemtime($this->fooUrl));
        $this->assertEquals(100, filectime($this->fooUrl));
        $this->assertFileTimesEqualStreamTimes($this->fooUrl, $file);
    }

    /**
     * @test
     * @group  issue_7
     * @group  issue_26
     */
    public function fileGetContentsChangesAttributeTimeOnly()
    {
        $file = vfsStream::newFile('foo.txt')
                         ->withContent('test')
                         ->at(StreamWrapper::getRoot())
                         ->lastModified(100)
                         ->lastAccessed(100)
                         ->lastAttributeModified(100);
        file_get_contents($this->fooUrl);
        $this->assertGreaterThan(time() - 2, fileatime($this->fooUrl));
        $this->assertLessThanOrEqual(time(), fileatime($this->fooUrl));
        $this->assertLessThanOrEqual(100, filemtime($this->fooUrl));
        $this->assertEquals(100, filectime($this->fooUrl));
        $this->assertFileTimesEqualStreamTimes($this->fooUrl, $file);
    }

    /**
     * @test
     * @group  issue_7
     * @group  issue_26
     */
    public function openFileWithTruncateChangesAttributeAndModificationTime()
    {
        $file = vfsStream::newFile('foo.txt')
                         ->withContent('test')
                         ->at(StreamWrapper::getRoot())
                         ->lastModified(100)
                         ->lastAccessed(100)
                         ->lastAttributeModified(100);
        fclose(fopen($this->fooUrl, 'wb'));
        $this->assertGreaterThan(time() - 2, filemtime($this->fooUrl));
        $this->assertGreaterThan(time() - 2, fileatime($this->fooUrl));
        $this->assertLessThanOrEqual(time(), filemtime($this->fooUrl));
        $this->assertLessThanOrEqual(time(), fileatime($this->fooUrl));
        $this->assertEquals(100, filectime($this->fooUrl));
        $this->assertFileTimesEqualStreamTimes($this->fooUrl, $file);
    }

    /**
     * @test
     * @group  issue_7
     */
    public function readFileChangesAccessTime()
    {
        $file = vfsStream::newFile('foo.txt')
                         ->withContent('test')
                         ->at(StreamWrapper::getRoot())
                         ->lastModified(100)
                         ->lastAccessed(100)
                         ->lastAttributeModified(100);
        $fp = fopen($this->fooUrl, 'rb');
        $openTime = time();
        sleep(2);
        fread($fp, 1024);
        fclose($fp);
        $this->assertLessThanOrEqual($openTime, filemtime($this->fooUrl));
        $this->assertLessThanOrEqual($openTime + 3, fileatime($this->fooUrl));
        $this->assertEquals(100, filectime($this->fooUrl));
        $this->assertFileTimesEqualStreamTimes($this->fooUrl, $file);
    }

    /**
     * @test
     * @group  issue_7
     */
    public function writeFileChangesModificationTime()
    {
        $file = vfsStream::newFile('foo.txt')
                         ->at(StreamWrapper::getRoot())
                         ->lastModified(100)
                         ->lastAccessed(100)
                         ->lastAttributeModified(100);
        $fp = fopen($this->fooUrl, 'wb');
        $openTime = time();
        sleep(2);
        fwrite($fp, 'test');
        fclose($fp);
        $this->assertLessThanOrEqual($openTime + 3, filemtime($this->fooUrl));
        $this->assertLessThanOrEqual($openTime, fileatime($this->fooUrl));
        $this->assertEquals(100, filectime($this->fooUrl));
        $this->assertFileTimesEqualStreamTimes($this->fooUrl, $file);

    }

    /**
     * @test
     * @group  issue_7
     */
    public function createNewFileSetsAllTimesToCurrentTime()
    {
        file_put_contents($this->fooUrl, 'test');
        $this->assertLessThanOrEqual(time(), filemtime($this->fooUrl));
        $this->assertEquals(fileatime($this->fooUrl), filectime($this->fooUrl));
        $this->assertEquals(fileatime($this->fooUrl), filemtime($this->fooUrl));
        $this->assertFileTimesEqualStreamTimes($this->fooUrl, StreamWrapper::getRoot()->getChild('foo.txt'));
    }

    /**
     * @test
     * @group  issue_7
     */
    public function createNewFileChangesAttributeAndModificationTimeOfContainingDirectory()
    {
        $dir = vfsStream::newDirectory('bar')
                        ->at(StreamWrapper::getRoot())
                        ->lastModified(100)
                        ->lastAccessed(100)
                        ->lastAttributeModified(100);
        file_put_contents($this->bazUrl, 'test');
        $this->assertLessThanOrEqual(time(), filemtime($this->barUrl));
        $this->assertLessThanOrEqual(time(), filectime($this->barUrl));
        $this->assertEquals(100, fileatime($this->barUrl));
        $this->assertFileTimesEqualStreamTimes($this->barUrl, $dir);
    }

    /**
     * @test
     * @group  issue_7
     */
    public function addNewFileNameWithLinkFunctionChangesAttributeTimeOfOriginalFile()
    {
        $this->markTestSkipped('Links are currently not supported by vfsStream.');
    }

    /**
     * @test
     * @group  issue_7
     */
    public function addNewFileNameWithLinkFunctionChangesAttributeAndModificationTimeOfDirectoryContainingLink()
    {
        $this->markTestSkipped('Links are currently not supported by vfsStream.');
    }

    /**
     * @test
     * @group  issue_7
     */
    public function removeFileChangesAttributeAndModificationTimeOfContainingDirectory()
    {
        $dir = vfsStream::newDirectory('bar')
                        ->at(StreamWrapper::getRoot());
        $file = vfsStream::newFile('baz.txt')
                         ->at($dir)
                         ->lastModified(100)
                         ->lastAccessed(100)
                         ->lastAttributeModified(100);
        $dir->lastModified(100)
            ->lastAccessed(100)
            ->lastAttributeModified(100);
        unlink($this->bazUrl);
        $this->assertLessThanOrEqual(time(), filemtime($this->barUrl));
        $this->assertLessThanOrEqual(time(), filectime($this->barUrl));
        $this->assertEquals(100, fileatime($this->barUrl));
        $this->assertFileTimesEqualStreamTimes($this->barUrl, $dir);
    }

    /**
     * @test
     * @group  issue_7
     */
    public function renameFileChangesAttributeAndModificationTimeOfAffectedDirectories()
    {
        $target = vfsStream::newDirectory('target')
                           ->at(StreamWrapper::getRoot())
                           ->lastModified(200)
                           ->lastAccessed(200)
                           ->lastAttributeModified(200);
        $source = vfsStream::newDirectory('bar')
                           ->at(StreamWrapper::getRoot());
        $file = vfsStream::newFile('baz.txt')
                         ->at($source)
                         ->lastModified(300)
                         ->lastAccessed(300)
                         ->lastAttributeModified(300);
        $source->lastModified(100)
               ->lastAccessed(100)
               ->lastAttributeModified(100);
        rename($this->bazUrl, vfsStream::url('root/target/baz.txt'));
        $this->assertLessThanOrEqual(time(), filemtime($this->barUrl));
        $this->assertLessThanOrEqual(time(), filectime($this->barUrl));
        $this->assertEquals(100, fileatime($this->barUrl));
        $this->assertFileTimesEqualStreamTimes($this->barUrl, $source);
        $this->assertLessThanOrEqual(time(), filemtime(vfsStream::url('root/target')));
        $this->assertLessThanOrEqual(time(), filectime(vfsStream::url('root/target')));
        $this->assertEquals(200, fileatime(vfsStream::url('root/target')));
        $this->assertFileTimesEqualStreamTimes(vfsStream::url('root/target'), $target);
    }

    /**
     * @test
     * @group  issue_7
     */
    public function renameFileDoesNotChangeFileTimesOfFileItself()
    {
        $target = vfsStream::newDirectory('target')
                           ->at(StreamWrapper::getRoot())
                           ->lastModified(200)
                           ->lastAccessed(200)
                           ->lastAttributeModified(200);
        $source = vfsStream::newDirectory('bar')
                           ->at(StreamWrapper::getRoot());
        $file = vfsStream::newFile('baz.txt')
                         ->at($source)
                         ->lastModified(300)
                         ->lastAccessed(300)
                         ->lastAttributeModified(300);
        $source->lastModified(100)
               ->lastAccessed(100)
               ->lastAttributeModified(100);
        rename($this->bazUrl, vfsStream::url('root/target/baz.txt'));
        $this->assertEquals(300, filemtime(vfsStream::url('root/target/baz.txt')));
        $this->assertEquals(300, filectime(vfsStream::url('root/target/baz.txt')));
        $this->assertEquals(300, fileatime(vfsStream::url('root/target/baz.txt')));
        $this->assertFileTimesEqualStreamTimes(vfsStream::url('root/target/baz.txt'), $file);
    }

    /**
     * @test
     * @group  issue_7
     */
    public function changeFileAttributesChangesAttributeTimeOfFileItself()
    {
        $this->markTestSkipped('Changing file attributes via stream wrapper for self-defined streams is not supported by PHP.');
    }
}
