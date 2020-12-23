<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\vfsStream;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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

use const DIRECTORY_SEPARATOR;

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
    protected function tearDown(): void
    {
        vfsStream::enableDotfiles();
    }

    /**
     * @return string[][]
     */
    public function provideSwitchWithExpectations(): array
    {
        return [
            [[vfsStream::class, 'disableDotfiles'], ['subdir', 'file2']],
            [[vfsStream::class, 'enableDotfiles'], ['.', '..', 'subdir', 'file2']],
        ];
    }

    private function assertDirectoryCount(int $expectedCount, int $actualCount): void
    {
        assertThat(
            $actualCount,
            equals($expectedCount),
            'Directory root contains ' . $expectedCount . ' children, but got ' . $actualCount
            . ' children while iterating over directory contents'
        );
    }

    /**
     * @param string[] $expectedDirectories
     *
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function directoryIteration(callable $switchDotFiles, array $expectedDirectories): void
    {
        $switchDotFiles();
        $dir = dir($this->root->url());
        $i = 0;
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
     * @param string[] $expectedDirectories
     *
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function directoryIterationWithDot(callable $switchDotFiles, array $expectedDirectories): void
    {
        $switchDotFiles();
        $dir = dir($this->root->url() . '/.');
        $i = 0;
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
     * @param string[] $expectedDirectories
     *
     * @test
     * @dataProvider  provideSwitchWithExpectations
     * @group  regression
     * @group  bug_2
     */
    public function directoryIterationWithOpenDir_Bug_2(callable $switchDotFiles, array $expectedDirectories): void
    {
        $switchDotFiles();
        $handle = opendir($this->root->url());
        $i = 0;
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
     * @param string[] $expectedDirectories
     *
     * @test
     * @dataProvider  provideSwitchWithExpectations
     * @group  regression
     * @group  bug_4
     */
    public function directoryIteration_Bug_4(callable $switchDotFiles, array $expectedDirectories): void
    {
        $switchDotFiles();
        $dir = $this->root->url();
        $list1 = [];
        $handle = opendir($dir);
        if ($handle !== false) {
            while (($listItem = readdir($handle)) !== false) {
                if ($listItem === '.' || $listItem === '..') {
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
        $handle = opendir($dir);
        if ($handle !== false) {
            while (($listItem = readdir($handle)) !== false) {
                if ($listItem === '.' || $listItem === '..') {
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
     * @param string[] $expectedDirectories
     *
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function directoryIterationShouldBeIndependent(callable $switchDotFiles, array $expectedDirectories): void
    {
        $switchDotFiles();
        $list1 = [];
        $list2 = [];
        $handle1 = opendir($this->root->url());
        $listItem = readdir($handle1);
        if ($listItem !== false) {
            $list1[] = $listItem;
        }

        $handle2 = opendir($this->root->url());
        $listItem = readdir($handle2);
        if ($listItem !== false) {
            $list2[] = $listItem;
        }

        $listItem = readdir($handle1);
        if ($listItem !== false) {
            $list1[] = $listItem;
        }

        $listItem = readdir($handle2);
        if ($listItem !== false) {
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
    public function recursiveDirectoryIterationWithDotsEnabled(): void
    {
        vfsStream::enableDotfiles();
        vfsStream::setup();
        $structure = [
            'Core' => [
                'AbstractFactory' => [
                    'test.php' => 'some text content',
                    'other.php' => 'Some more text content',
                    'Invalid.csv' => 'Something else',
                ],
                'AnEmptyFolder' => [],
                'badlocation.php' => 'some bad content',
            ],
        ];
        $root = vfsStream::create($structure);
        $rootPath = vfsStream::url($root->getName());

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        $pathes = [];
        foreach ($iterator as $fullFileName => $fileSPLObject) {
            $pathes[] = $fullFileName;
        }

        $cordDir = 'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR;
        assertThat($pathes, equals([
            'vfs://root' . DIRECTORY_SEPARATOR . '.',
            'vfs://root' . DIRECTORY_SEPARATOR . '..',
            $cordDir . '.',
            $cordDir . '..',
            $cordDir . 'AbstractFactory' . DIRECTORY_SEPARATOR . '.',
            $cordDir . 'AbstractFactory' . DIRECTORY_SEPARATOR . '..',
            $cordDir . 'AbstractFactory' . DIRECTORY_SEPARATOR . 'test.php',
            $cordDir . 'AbstractFactory' . DIRECTORY_SEPARATOR . 'other.php',
            $cordDir . 'AbstractFactory' . DIRECTORY_SEPARATOR . 'Invalid.csv',
            $cordDir . 'AbstractFactory',
            $cordDir . 'AnEmptyFolder' . DIRECTORY_SEPARATOR . '.',
            $cordDir . 'AnEmptyFolder' . DIRECTORY_SEPARATOR . '..',
            $cordDir . 'AnEmptyFolder',
            $cordDir . 'badlocation.php',
            'vfs://root' . DIRECTORY_SEPARATOR . 'Core',
        ]));
    }

    /**
     * @test
     * @group  issue_50
     */
    public function recursiveDirectoryIterationWithDotsDisabled(): void
    {
        vfsStream::disableDotfiles();
        vfsStream::setup();
        $structure = [
            'Core' => [
                'AbstractFactory' => [
                    'test.php' => 'some text content',
                    'other.php' => 'Some more text content',
                    'Invalid.csv' => 'Something else',
                ],
                'AnEmptyFolder' => [],
                'badlocation.php' => 'some bad content',
            ],
        ];
        $root = vfsStream::create($structure);
        $rootPath = vfsStream::url($root->getName());

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        $pathes = [];
        foreach ($iterator as $fullFileName => $fileSPLObject) {
            $pathes[] = $fullFileName;
        }

        $coreDir = 'vfs://root' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR;
        assertThat(
            $pathes,
            equals([
                $coreDir . 'AbstractFactory' . DIRECTORY_SEPARATOR . 'test.php',
                $coreDir . 'AbstractFactory' . DIRECTORY_SEPARATOR . 'other.php',
                $coreDir . 'AbstractFactory' . DIRECTORY_SEPARATOR . 'Invalid.csv',
                $coreDir . 'AbstractFactory',
                $coreDir . 'AnEmptyFolder',
                $coreDir . 'badlocation.php',
                'vfs://root' . DIRECTORY_SEPARATOR . 'Core',
            ])
        );
    }
}
