<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests;

use bovigo\callmap\NewInstance;
use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamContent;
use bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertFalse;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isSameAs;
use function is_string;

/**
 * Test for bovigo\vfs\vfsStreamContainerIterator.
 */
class vfsStreamContainerIteratorTestCase extends TestCase
{
    /**
     * instance to test
     *
     * @var vfsStreamDirectory
     */
    private $dir;
    /**
     * child one
     *
     * @var vfsStreamContent
     */
    private $child1;
    /**
     * child two
     *
     * @var vfsStreamContent
     */
    private $child2;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $this->dir = new vfsStreamDirectory('foo');
        $this->child1 = NewInstance::of(vfsStreamContent::class)->returns(['getName' => 'bar']);

        $this->dir->addChild($this->child1);
        $this->child2 = NewInstance::of(vfsStreamContent::class)->returns(['getName' => 'baz']);
        $this->dir->addChild($this->child2);
    }

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
            [[vfsStream::class, 'disableDotfiles'], []],
            [[vfsStream::class, 'enableDotfiles'], ['.', '..']],
        ];
    }

    /**
     * @param string|vfsStreamContent $dir
     */
    private function nameOf($dir): string
    {
        if (is_string($dir)) {
            return $dir;
        }

        return $dir->getName();
    }

    /**
     * @param  mixed[] $dirs
     *
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function iteration(callable $switchDotFiles, array $dirs): void
    {
        $dirs[] = $this->child1;
        $dirs[] = $this->child2;
        $switchDotFiles();
        $dirIterator = $this->dir->getIterator();
        foreach ($dirs as $dir) {
            assertThat($dirIterator->key(), equals($this->nameOf($dir)));
            assertTrue($dirIterator->valid());
            if (! is_string($dir)) {
                assertThat($dirIterator->current(), isSameAs($dir));
            }

            $dirIterator->next();
        }

        assertFalse($dirIterator->valid());
        assertNull($dirIterator->key());
        assertNull($dirIterator->current());
    }
}
