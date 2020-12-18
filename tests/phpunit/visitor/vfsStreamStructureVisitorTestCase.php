<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests\visitor;

use bovigo\vfs\vfsStream;
use bovigo\vfs\visitor\vfsStreamStructureVisitor;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;

/**
 * Test for bovigo\vfs\visitor\vfsStreamStructureVisitor.
 *
 * @see    https://github.com/mikey179/vfsStream/issues/10
 *
 * @since  0.10.0
 * @group  issue_10
 */
class vfsStreamStructureVisitorTestCase extends TestCase
{
    /** @var vfsStreamStructureVisitor */
    private $structureVisitor;

    protected function setUp(): void
    {
        $this->structureVisitor = new vfsStreamStructureVisitor();
    }

    /**
     * @test
     */
    public function visitFileCreatesStructureForFile(): void
    {
        assertThat(
            $this->structureVisitor->visitFile(
                vfsStream::newFile('foo.txt')->withContent('test')
            )->getStructure(),
            equals(['foo.txt' => 'test'])
        );
    }

    /**
     * @test
     */
    public function visitFileCreatesStructureForBlock(): void
    {
        assertThat(
            $this->structureVisitor->visitBlockDevice(
                vfsStream::newBlock('foo')->withContent('test')
            )->getStructure(),
            equals(['[foo]' => 'test'])
        );
    }

    /**
     * @test
     */
    public function visitDirectoryCreatesStructureForDirectory(): void
    {
        assertThat(
            $this->structureVisitor->visitDirectory(
                vfsStream::newDirectory('baz')
            )->getStructure(),
            equals(['baz' => []])
        );
    }

    /**
     * @test
     */
    public function visitRecursiveDirectoryStructure(): void
    {
        $structure = [
            'root' => [
                'test' => [
                    'foo' => ['test.txt' => 'hello'],
                    'baz.txt' => 'world',
                ],
                'foo.txt' => '',
            ],
        ];
        $root = vfsStream::setup('root', null, $structure['root']);
        assertThat(
            $this->structureVisitor->visitDirectory($root)->getStructure(),
            equals($structure)
        );
    }
}
