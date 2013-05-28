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
/**
 * Test for directory iteration.
 *
 * @group  dir
 * @group  iteration
 */
class DirectoryIterationTestCase extends vfsStreamWrapperBaseTestCase
{
    /**
     * clean up test environment
     */
    public function tearDown()
    {
        vfsStream::enableDotfiles();
    }

    /**
     * @return  array
     */
    public function provideSwitchWithExpectations()
    {
        return array(array(function() { vfsStream::disableDotfiles(); }, array('bar', 'baz2')),
                     array(function() { vfsStream::enableDotfiles(); }, array('.', '..', 'bar', 'baz2'))
        );
    }

    /**
     * assertion for directoy count
     *
     * @param  int  $expectedCount
     * @param  int  $actualCount
     */
    private function assertDirectoryCount($expectedCount, $actualCount)
    {
        $this->assertEquals($expectedCount,
                            $actualCount,
                            'Directory foo contains ' . $expectedCount . ' children, but got ' . $actualCount . ' children while iterating over directory contents'
        );
    }

    /**
     * @param  \Closure  $dotFilesSwitch
     * @param  string[]  $expectedDirectories
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function directoryIteration(\Closure $dotFilesSwitch, array $expectedDirectories)
    {
        $dotFilesSwitch();
        $dir = dir($this->fooURL);
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            $this->assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        $dir->rewind();
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            $this->assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        $dir->close();
    }

    /**
     * @param  \Closure  $dotFilesSwitch
     * @param  string[]  $expectedDirectories
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function directoryIterationWithDot(\Closure $dotFilesSwitch, array $expectedDirectories)
    {
        $dotFilesSwitch();
        $dir = dir($this->fooURL . '/.');
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            $this->assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        $dir->rewind();
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            $this->assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        $dir->close();
    }

    /**
     * assure that a directory iteration works as expected
     *
     * @param  \Closure  $dotFilesSwitch
     * @param  string[]  $expectedDirectories
     * @test
     * @dataProvider  provideSwitchWithExpectations
     * @group  regression
     * @group  bug_2
     */
    public function directoryIterationWithOpenDir_Bug_2(\Closure $dotFilesSwitch, array $expectedDirectories)
    {
        $dotFilesSwitch();
        $handle = opendir($this->fooURL);
        $i   = 0;
        while (false !== ($entry = readdir($handle))) {
            $i++;
            $this->assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);

        rewind($handle);
        $i   = 0;
        while (false !== ($entry = readdir($handle))) {
            $i++;
            $this->assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        closedir($handle);
    }

    /**
     * assure that a directory iteration works as expected
     *
     * @author  Christoph Bloemer
     * @param  \Closure  $dotFilesSwitch
     * @param  string[]  $expectedDirectories
     * @test
     * @dataProvider  provideSwitchWithExpectations
     * @group  regression
     * @group  bug_4
     */
    public function directoryIteration_Bug_4(\Closure $dotFilesSwitch, array $expectedDirectories)
    {
        $dotFilesSwitch();
        $dir   = $this->fooURL;
        $list1 = array();
        if ($handle = opendir($dir)) {
            while (false !== ($listItem = readdir($handle))) {
                if ('.'  != $listItem && '..' != $listItem) {
                    if (is_file($dir . '/' . $listItem) === true) {
                        $list1[] = 'File:[' . $listItem . ']';
                    } elseif (is_dir($dir . '/' . $listItem) === true) {
                        $list1[] = 'Folder:[' . $listItem . ']';
                    }
                }
            }

            closedir($handle);
        }

        $list2 = array();
        if ($handle = opendir($dir)) {
            while (false !== ($listItem = readdir($handle))) {
                if ('.'  != $listItem && '..' != $listItem) {
                    if (is_file($dir . '/' . $listItem) === true) {
                        $list2[] = 'File:[' . $listItem . ']';
                    } elseif (is_dir($dir . '/' . $listItem) === true) {
                        $list2[] = 'Folder:[' . $listItem . ']';
                    }
                }
            }

            closedir($handle);
        }

        $this->assertEquals($list1, $list2);
        $this->assertEquals(2, count($list1));
        $this->assertEquals(2, count($list2));
    }

    /**
     * assure that a directory iteration works as expected
     *
     * @param  \Closure  $dotFilesSwitch
     * @param  string[]  $expectedDirectories
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function directoryIterationShouldBeIndependent(\Closure $dotFilesSwitch, array $expectedDirectories)
    {
        $dotFilesSwitch();
        $list1   = array();
        $list2   = array();
        $handle1 = opendir($this->fooURL);
        if (false !== ($listItem = readdir($handle1))) {
            $list1[] = $listItem;
        }

        $handle2 = opendir($this->fooURL);
        if (false !== ($listItem = readdir($handle2))) {
            $list2[] = $listItem;
        }

        if (false !== ($listItem = readdir($handle1))) {
            $list1[] = $listItem;
        }

        if (false !== ($listItem = readdir($handle2))) {
            $list2[] = $listItem;
        }

        closedir($handle1);
        closedir($handle2);
        $this->assertEquals($list1, $list2);
        $this->assertEquals(2, count($list1));
        $this->assertEquals(2, count($list2));
    }
}
?>