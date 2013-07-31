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
 * Test for vfsStream_Abstract_Visitor.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 * @group  issue_10
 */
class vfsStreamAbstractVisitorTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @var  vfsStream_Abstract_Visitor
     */
    protected $abstractVisitor;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->abstractVisitor = $this->getMock('vfsStream_Abstract_Visitor',
                                                array('visitFile', 'visitDirectory')
                                 );
    }

    /**
     * @test
     * @expectedException  \InvalidArgumentException
     */
    public function visitThrowsInvalidArgumentExceptionOnUnknownContentType()
    {
        $mockContent = $this->getMock('vfsStream_Interface_Content');
        $mockContent->expects($this->any())
                    ->method('getType')
                    ->will($this->returnValue('invalid'));
        $this->assertSame($this->abstractVisitor,
                          $this->abstractVisitor->visit($mockContent)
        );
    }

    /**
     * @test
     */
    public function visitWithFileCallsVisitFile()
    {
        $file = new vfsStream_File('foo.txt');
        $this->abstractVisitor->expects($this->once())
                              ->method('visitFile')
                              ->with($this->equalTo($file));
        $this->assertSame($this->abstractVisitor,
                          $this->abstractVisitor->visit($file)
        );
    }

    /**
     * @test
     */
    public function visitWithDirectoryCallsVisitDirectory()
    {
        $dir = new vfsStream_Directory('bar');
        $this->abstractVisitor->expects($this->once())
                              ->method('visitDirectory')
                              ->with($this->equalTo($dir));
        $this->assertSame($this->abstractVisitor,
                          $this->abstractVisitor->visit($dir)
        );
    }
}
?>
