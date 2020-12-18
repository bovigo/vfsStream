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
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use UnexpectedValueException;

use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\contains;
use function bovigo\assert\predicate\equals;
use function mkdir;

use const DIRECTORY_SEPARATOR;

/**
 * Test for directory iteration.
 *
 * @group  issue_104
 * @group  issue_128
 * @since  1.6.2
 */
class FilenameTestCase extends TestCase
{
    /** @var string */
    private $rootDir;

    /** @var string */
    private $lostAndFound;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        vfsStream::setup('root');
        $this->rootDir = vfsStream::url('root');
        $this->lostAndFound = $this->rootDir . '/lost+found/';
        mkdir($this->lostAndFound);
    }

    /**
     * @test
     */
    public function worksWithCorrectName(): void
    {
        $results = [];
        $it = new RecursiveDirectoryIterator($this->lostAndFound);
        foreach ($it as $f) {
            $results[] = $f->getPathname();
        }

        assertThat($results, equals([
            'vfs://root/lost+found' . DIRECTORY_SEPARATOR . '.',
            'vfs://root/lost+found' . DIRECTORY_SEPARATOR . '..',
        ]));
    }

    /**
     * @test
     */
    public function doesNotWorkWithInvalidName(): void
    {
        expect(function (): void {
            new RecursiveDirectoryIterator($this->rootDir . '/lost found/');
        })->throws(UnexpectedValueException::class)
            //PHP8 error starts with capital F, so just check the last part.
          ->message(contains('ailed to open dir'));
    }

    /**
     * @test
     */
    public function returnsCorrectNames(): void
    {
        $results = [];
        $it = new RecursiveDirectoryIterator($this->rootDir);
        foreach ($it as $f) {
            $results[] = $f->getPathname();
        }

        assertThat($results, equals([
            'vfs://root' . DIRECTORY_SEPARATOR . '.',
            'vfs://root' . DIRECTORY_SEPARATOR . '..',
            'vfs://root' . DIRECTORY_SEPARATOR . 'lost+found',
        ]));
    }
}
