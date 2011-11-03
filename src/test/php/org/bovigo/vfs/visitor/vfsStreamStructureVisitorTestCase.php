<?php
/**
 * Test for org::bovigo::vfs::visitor::vfsStreamStructureVisitor.
 *
 * @package     bovigo_vfs
 * @subpackage  visitor_test
 */
require_once 'org/bovigo/vfs/visitor/vfsStreamStructureVisitor.php';
require_once 'PHPUnit/Framework/TestCase.php';
/**
 * Test for org::bovigo::vfs::visitor::vfsStreamStructureVisitor.
 *
 * @package     bovigo_vfs
 * @subpackage  visitor_test
 * @since       0.10.0
 * @see         https://github.com/mikey179/vfsStream/issues/10
 * @group       issue_10
 */
class vfsStreamStructureVisitorTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function visitFileCreatesStructureForFile()
    {
        $structureVisitor = new vfsStreamStructureVisitor();
        $this->assertEquals(array('foo.txt' => 'test'),
                            $structureVisitor->visitFile(vfsStream::newFile('foo.txt')
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
        $structureVisitor = new vfsStreamStructureVisitor();
        $this->assertEquals(array('baz' => array()),
                            $structureVisitor->visitDirectory(vfsStream::newDirectory('baz'))
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
        $structureVisitor = new vfsStreamStructureVisitor();
        $this->assertEquals(array('root' => array('test' => array('foo'     => array('test.txt' => 'hello'),
                                                                  'baz.txt' => 'world'
                                                                               ),
                                                                  'foo.txt' => ''
                                            ),
                            ),
                            $structureVisitor->visitDirectory($root)
                                             ->getStructure()
        );
    }
}
?>