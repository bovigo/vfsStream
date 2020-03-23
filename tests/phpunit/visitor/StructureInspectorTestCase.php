<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs\tests\visitor;

use bovigo\vfs\vfsStream;
use bovigo\vfs\visitor\StructureInspector;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;

/**
 * Test for bovigo\vfs\visitor\StructureInspector.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 * @group  issue_10
 */
class StructureInspectorTestCase extends \BC_PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function visitFileCreatesStructureForFile()
    {
        $structureInspector = new StructureInspector();
        $this->assertEquals(array('foo.txt' => 'test'),
                            $structureInspector->visitFile(vfsStream::newFile('foo.txt')
                                                                  ->withContent('test')
                                               )
                                             ->getStructure()
        );
    }

    /**
     * @test
     */
    public function visitFileCreatesStructureForBlock()
    {
        $structureInspector = new StructureInspector();
        $this->assertEquals(array('[foo]' => 'test'),
                            $structureInspector->visitBlockDevice(vfsStream::newBlock('foo')
                                                                  ->withContent('test')
                                                 )
                                               ->getStructure()
        );
    }

    /**
     * @test
     */
    public function visitDirectoryCreatesStructureForDirectory()
    {
        $structureInspector = new StructureInspector();
        $this->assertEquals(array('baz' => array()),
                            $structureInspector->visitDirectory(vfsStream::newDirectory('baz'))
                                               ->getStructure()
        );
    }

    /**
     * @test
     */
    public function visitRecursiveDirectoryStructure()
    {
        $root         = vfsStream::setup('root',
                                         null,
                                         array('test' => array('foo'     => array('test.txt' => 'hello'),
                                                               'baz.txt' => 'world'
                                                         ),
                                               'foo.txt' => ''
                                         )
                        );
        $structureInspector = new StructureInspector();
        $this->assertEquals(array('root' => array('test' => array('foo'     => array('test.txt' => 'hello'),
                                                                  'baz.txt' => 'world'
                                                                               ),
                                                                  'foo.txt' => ''
                                            ),
                            ),
                            $structureInspector->visitDirectory($root)
                                               ->getStructure()
        );
    }
}
