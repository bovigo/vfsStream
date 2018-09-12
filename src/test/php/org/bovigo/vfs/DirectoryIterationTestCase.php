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

use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isOfSize;
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

    public function provideSwitchWithExpectations(): array
    {
        return [
            [[vfsStream::class, 'disableDotfiles'], ['subdir', 'file2']],
            [[vfsStream::class, 'enableDotfiles'], ['.', '..', 'subdir', 'file2']]
        ];
    }

    private function assertDirectoryCount(int $expectedCount, int $actualCount)
    {
        assertThat(
            $actualCount,
            equals($expectedCount),
            'Directory root contains ' . $expectedCount . ' children, but got ' . $actualCount . ' children while iterating over directory contents'
        );
    }

    /**
     * @param  callable  $switchDotFiles
     * @param  string[]  $expectedDirectories
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function directoryIteration(callable $switchDotFiles, array $expectedDirectories)
    {
        $switchDotFiles();
        $dir = dir($this->root->url());
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        $dir->rewind();
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        $dir->close();
    }

    /**
     * @param  callable  $switchDotFiles
     * @param  string[]  $expectedDirectories
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function directoryIterationWithDot(callable $switchDotFiles, array $expectedDirectories)
    {
        $switchDotFiles();
        $dir = dir($this->root->url() . '/.');
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        $dir->rewind();
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        $dir->close();
    }

    /**
     * @param  callable  $switchDotFiles
     * @param  string[]  $expectedDirectories
     * @test
     * @dataProvider  provideSwitchWithExpectations
     * @group  regression
     * @group  bug_2
     */
    public function directoryIterationWithOpenDir_Bug_2(callable $switchDotFiles, array $expectedDirectories)
    {
        $switchDotFiles();
        $handle = opendir($this->root->url());
        $i   = 0;
        while (false !== ($entry = readdir($handle))) {
            $i++;
            assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);

        rewinddir($handle);
        $i   = 0;
        while (false !== ($entry = readdir($handle))) {
            $i++;
            assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        closedir($handle);
    }

    /**
     * @author  Christoph Bloemer
     * @param  callable  $switchDotFiles
     * @param  string[]  $expectedDirectories
     * @test
     * @dataProvider  provideSwitchWithExpectations
     * @group  regression
     * @group  bug_4
     */
    public function directoryIteration_Bug_4(callable $switchDotFiles, array $expectedDirectories)
    {
        $switchDotFiles();
        $dir   = $this->root->url();
        $list1 = [];
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

        $list2 = [];
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

        assertThat($list1, equals($list2));
        assertThat($list1, isOfSize(2));
    }

    /**
     * @param  callable  $switchDotFiles
     * @param  string[]  $expectedDirectories
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function directoryIterationShouldBeIndependent(callable $switchDotFiles, array $expectedDirectories)
    {
        $switchDotFiles();
        $list1   = [];
        $list2   = [];
        $handle1 = opendir($this->root->url());
        if (false !== ($listItem = readdir($handle1))) {
            $list1[] = $listItem;
        }

        $handle2 = opendir($this->root->url());
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
        assertThat($list1, equals($list2));
        assertThat($list1, isOfSize(2));
    }

    /**
     * @test
     * @group  issue_50
     */
    public function recursiveDirectoryIterationWithDotsEnabled()
    {
        vfsStream::enableDotfiles();
        vfsStream::setup();
        $structure = [
          'Core' => [
            'AbstractFactory' => [
              'test.php'    => 'some text content',
              'other.php'   => 'Some more text content',
              'Invalid.csv' => 'Something else',
             ],
            'AnEmptyFolder'   => [],
            'badlocation.php' => 'some bad content',
          ]
        ];
        $root     = vfsStream::create($structure);
        $rootPath = vfsStream::url($root->getName());

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        $pathes = [];
        foreach ($iterator as $fullFileName => $fileSPLObject) {
            $pathes[] = $fullFileName;
        }

        assertThat($pathes, equals([
            'vfs://root'.DIRECTORY_SEPARATOR.'.',
            'vfs://root'.DIRECTORY_SEPARATOR.'..',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'.',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'..',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'AbstractFactory'.DIRECTORY_SEPARATOR.'.',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'AbstractFactory'.DIRECTORY_SEPARATOR.'..',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'AbstractFactory'.DIRECTORY_SEPARATOR.'test.php',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'AbstractFactory'.DIRECTORY_SEPARATOR.'other.php',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'AbstractFactory'.DIRECTORY_SEPARATOR.'Invalid.csv',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'AbstractFactory',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'AnEmptyFolder'.DIRECTORY_SEPARATOR.'.',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'AnEmptyFolder'.DIRECTORY_SEPARATOR.'..',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'AnEmptyFolder',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'badlocation.php',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'
        ]));
    }

    /**
     * @test
     * @group  issue_50
     */
    public function recursiveDirectoryIterationWithDotsDisabled()
    {
        vfsStream::disableDotfiles();
        vfsStream::setup();
        $structure = [
          'Core' => [
            'AbstractFactory' => [
              'test.php'    => 'some text content',
              'other.php'   => 'Some more text content',
              'Invalid.csv' => 'Something else',
            ],
            'AnEmptyFolder'   => [],
            'badlocation.php' => 'some bad content',
          ]
        ];
        $root     = vfsStream::create($structure);
        $rootPath = vfsStream::url($root->getName());

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootPath),
                                                   \RecursiveIteratorIterator::CHILD_FIRST);
        $pathes = [];
        foreach ($iterator as $fullFileName => $fileSPLObject) {
            $pathes[] = $fullFileName;
        }

        assertThat($pathes, equals([
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'AbstractFactory'.DIRECTORY_SEPARATOR.'test.php',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'AbstractFactory'.DIRECTORY_SEPARATOR.'other.php',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'AbstractFactory'.DIRECTORY_SEPARATOR.'Invalid.csv',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'AbstractFactory',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'AnEmptyFolder',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'badlocation.php',
            'vfs://root'.DIRECTORY_SEPARATOR.'Core'
        ]));
    }
}
