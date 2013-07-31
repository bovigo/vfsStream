<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
require_once __DIR__ . '/../../bootstrap/default.php';
/**
 * Test for vfsStream_Visitor_Print.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 * @group  issue_10
 */
class vfsStreamVisitorPrintTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException  \InvalidArgumentException
     */
    public function constructWithNonResourceThrowsInvalidArgumentException()
    {
        new vfsStream_Visitor_Print('invalid');
    }

    /**
     * @test
     * @expectedException  \InvalidArgumentException
     */
    public function constructWithNonStreamResourceThrowsInvalidArgumentException()
    {
        new vfsStream_Visitor_Print(xml_parser_create());
    }

    /**
     * @test
     */
    public function visitFileWritesFileNameToStream()
    {
        $output       = vfsStream::newFile('foo.txt')
                                       ->at(vfsStream::setup());
        $printVisitor = new vfsStream_Visitor_Print(fopen('vfs://root/foo.txt', 'wb'));
        $this->assertSame($printVisitor,
                          $printVisitor->visitFile(vfsStream::newFile('bar.txt'))
        );
        $this->assertEquals("- bar.txt\n", $output->getContent());
    }

    /**
     * @test
     */
    public function visitDirectoryWritesDirectoryNameToStream()
    {
        $output       = vfsStream::newFile('foo.txt')
                                       ->at(vfsStream::setup());
        $printVisitor = new vfsStream_Visitor_Print(fopen('vfs://root/foo.txt', 'wb'));
        $this->assertSame($printVisitor,
                          $printVisitor->visitDirectory(vfsStream::newDirectory('baz'))
        );
        $this->assertEquals("- baz\n", $output->getContent());
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
        $printVisitor = new vfsStream_Visitor_Print(fopen('vfs://root/foo.txt', 'wb'));
        $this->assertSame($printVisitor,
                          $printVisitor->visitDirectory($root)
        );
        $this->assertEquals("- root\n  - test\n    - foo\n      - test.txt\n    - baz.txt\n  - foo.txt\n", file_get_contents('vfs://root/foo.txt'));
    }
}
?>
