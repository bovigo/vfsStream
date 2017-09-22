<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
namespace org\bovigo\vfs\visitor;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assert;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Test for org\bovigo\vfs\visitor\vfsStreamStructureVisitor.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 * @group  issue_10
 */
class vfsStreamStructureVisitorTestCase extends TestCase
{
    private $structureVisitor;

    public function setup()
    {
        $this->structureVisitor = new vfsStreamStructureVisitor();
    }
    /**
     * @test
     */
    public function visitFileCreatesStructureForFile()
    {
        assert(
            $this->structureVisitor->visitFile(
                vfsStream::newFile('foo.txt')->withContent('test')
            )->getStructure(),
            equals(['foo.txt' => 'test'])
        );
    }

    /**
     * @test
     */
    public function visitFileCreatesStructureForBlock()
    {
        assert(
            $this->structureVisitor->visitBlockDevice(
                vfsStream::newBlock('foo')->withContent('test')
            )->getStructure(),
            equals(['[foo]' => 'test'])
        );
    }

    /**
     * @test
     */
    public function visitDirectoryCreatesStructureForDirectory()
    {
        assert(
            $this->structureVisitor->visitDirectory(
                  vfsStream::newDirectory('baz')
            )->getStructure(),
            equals(['baz' => []])
        );
    }

    /**
     * @test
     */
    public function visitRecursiveDirectoryStructure()
    {
        $structure = [
          'root' => ['test' => [
                        'foo'     => ['test.txt' => 'hello'],
                        'baz.txt' => 'world'
                    ],
                    'foo.txt' => ''
        ]];
        $root = vfsStream::setup('root', null, $structure['root']);
        assert(
            $this->structureVisitor->visitDirectory($root)->getStructure(),
            equals($structure)
        );
    }
}
