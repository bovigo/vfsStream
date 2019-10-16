<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace org\bovigo\vfs;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use const DIRECTORY_SEPARATOR;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isOfSize;
use function closedir;
use function count;
use function dir;
use function in_array;
use function is_dir;
use function is_file;
use function opendir;
use function readdir;
use function rewinddir;

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
    protected function tearDown() : void
    {
        vfsStream::enableDotfiles();
    }

    public function provideSwitchWithExpectations() : array
    {
        return [
            [[vfsStream::class, 'disableDotfiles'], ['subdir', 'file2']],
            [[vfsStream::class, 'enableDotfiles'], ['.', '..', 'subdir', 'file2']],
        ];
    }

    private function assertDirectoryCount(int $expectedCount, int $actualCount) : void
    {
        assertThat(
            $actualCount,
            equals($expectedCount),
            'Directory root contains ' . $expectedCount . ' children, but got ' . $actualCount . ' children while iterating over directory contents'
        );
    }

    /**
     * @param  string[] $expectedDirectories
     *
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function directoryIteration(callable $switchDotFiles, array $expectedDirectories) : void
    {
        $switchDotFiles();
        $dir = dir($this->root->url());
        $i   = 0;
        while (($entry = $dir->read()) !== false) {
            $i++;
            assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        $dir->rewind();
        $i = 0;
        while (($entry = $dir->read()) !== false) {
            $i++;
            assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        $dir->close();
    }

    /**
     * @param  string[] $expectedDirectories
     *
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function directoryIterationWithDot(callable $switchDotFiles, array $expectedDirectories) : void
    {
        $switchDotFiles();
        $dir = dir($this->root->url() . '/.');
        $i   = 0;
        while (($entry = $dir->read()) !== false) {
            $i++;
            assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        $dir->rewind();
        $i = 0;
        while (($entry = $dir->read()) !== false) {
            $i++;
            assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        $dir->close();
    }

    /**
     * @param  string[] $expectedDirectories
     *
     * @test
     * @dataProvider  provideSwitchWithExpectations
     * @group  regression
     * @group  bug_2
     */
    public function directoryIterationWithOpenDir_Bug_2(callable $switchDotFiles, array $expectedDirectories) : void
    {
        $switchDotFiles();
        $handle = opendir($this->root->url());
        $i      = 0;
        while (($entry = readdir($handle)) !== false) {
            $i++;
            assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);

        rewinddir($handle);
        $i = 0;
        while (($entry = readdir($handle)) !== false) {
            $i++;
            assertTrue(in_array($entry, $expectedDirectories));
        }

        $this->assertDirectoryCount(count($expectedDirectories), $i);
        closedir($handle);
    }

    /**
     * @param  string[] $expectedDirectories
     *
     * @test
     * @dataProvider  provideSwitchWithExpectations
     * @group  regression
     * @group  bug_4
     */
    public function directoryIteration_Bug_4(callable $switchDotFiles, array $expectedDirectories) : void
    {
        $switchDotFiles();
        $dir   = $this->root->url();
        $list1 = [];
        if ($handle = opendir($dir)) {
            while (($listItem = readdir($handle)) !== false) {
                if ($listItem  === '.' || $listItem === '..') {
                    continue;
                }

                if (is_file($dir . '/' . $listItem) === true) {
                    $list1[] = 'File:[' . $listItem . ']';
                } elseif (is_dir($dir . '/' . $listItem) === true) {
                    $list1[] = 'Folder:[' . $listItem . ']';
                }
            }

            closedir($handle);
        }

        $list2 = [];
        if ($handle = opendir($dir)) {
            while (($listItem = readdir($handle)) !== false) {
                if ($listItem  === '.' || $listItem === '..') {
                    continue;
                }

                if (is_file($dir . '/' . $listItem) === true) {
                    $list2[] = 'File:[' . $listItem . ']';
                } elseif (is_dir($dir . '/' . $listItem) === true) {
                    $list2[] = 'Folder:[' . $listItem . ']';
                }
            }

            closedir($handle);
        }

        assertThat($list1, equals($list2));
        assertThat($list1, isOfSize(2));
    }

    /**
     * @param  string[] $expectedDirectories
     *
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function directoryIterationShouldBeIndependent(callable $switchDotFiles, array $expectedDirectories) : void
    {
        $switchDotFiles();
        $list1   = [];
        $list2   = [];
        $handle1 = opendir($this->root->url());
        if (($listItem = readdir($handle1)) !== false) {
            $list1[] = $listItem;
        }

        $handle2 = opendir($this->root->url());
        if (($listItem = readdir($handle2)) !== false) {
            $list2[] = $listItem;
        }

        if (($listItem = readdir($handle1)) !== false) {
            $list1[] = $listItem;
        }

        if (($listItem = readdir($handle2)) !== false) {
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
    public function recursiveDirectoryIterationWithDotsEnabled() : void
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
            ],
        ];
        $root      = vfsStream::create($structure);
        $rootPath  = vfsStream::url($root->getName());

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        $pathes   = [];
        foreach ($iterator as $fullFileName => $fileSPLObject) {
            $pathes[] = $fullFileName;
        }

        assertThat($pathes, equals([
            'vfs://root' . DIRECTORY_SEPARATOR . '.',
            'vfs://root' . DIRECTORY_SEPARATOR . '..',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . '.',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . '..',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'AbstractFactory' . DIRECTORY_SEPARATOR . '.',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'AbstractFactory' . DIRECTORY_SEPARATOR . '..',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'AbstractFactory' . DIRECTORY_SEPARATOR . 'test.php',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'AbstractFactory' . DIRECTORY_SEPARATOR . 'other.php',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'AbstractFactory' . DIRECTORY_SEPARATOR . 'Invalid.csv',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'AbstractFactory',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'AnEmptyFolder' . DIRECTORY_SEPARATOR . '.',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'AnEmptyFolder' . DIRECTORY_SEPARATOR . '..',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'AnEmptyFolder',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'badlocation.php',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core',
        ]));
    }

    /**
     * @test
     * @group  issue_50
     */
    public function recursiveDirectoryIterationWithDotsDisabled() : void
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
            ],
        ];
        $root      = vfsStream::create($structure);
        $rootPath  = vfsStream::url($root->getName());

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        $pathes   = [];
        foreach ($iterator as $fullFileName => $fileSPLObject) {
            $pathes[] = $fullFileName;
        }

        assertThat($pathes, equals([
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'AbstractFactory' . DIRECTORY_SEPARATOR . 'test.php',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'AbstractFactory' . DIRECTORY_SEPARATOR . 'other.php',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'AbstractFactory' . DIRECTORY_SEPARATOR . 'Invalid.csv',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'AbstractFactory',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'AnEmptyFolder',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'badlocation.php',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core',
        ]));
    }
}
