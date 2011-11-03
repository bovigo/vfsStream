<?php
/**
 * Test for org::bovigo::vfs::visitor::vfsStreamAbstractVisitor.
 *
 * @package     bovigo_vfs
 * @subpackage  visitor_test
 */
require_once 'org/bovigo/vfs/visitor/vfsStreamAbstractVisitor.php';
require_once 'PHPUnit/Framework/TestCase.php';
/**
 * Test for org::bovigo::vfs::visitor::vfsStreamAbstractVisitor.
 *
 * @package     bovigo_vfs
 * @subpackage  visitor_test
 * @since       0.10.0
 * @see         https://github.com/mikey179/vfsStream/issues/10
 * @group       issue_10
 */
class vfsStreamAbstractVisitorTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @var  vfsStreamAbstractVisitor
     */
    protected $abstractVisitor;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->abstractVisitor = $this->getMock('vfsStreamAbstractVisitor',
                                                array('visitFile', 'visitDirectory')
                                 );
    }

    /**
     * @test
     * @expectedException  InvalidArgumentException
     */
    public function visitThrowsInvalidArgumentExceptionOnUnknownContentType()
    {
        $mockContent = $this->getMock('vfsStreamContent');
        $mockContent->expects($this->once())
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
        $file = new vfsStreamFile('foo.txt');
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
        $dir = new vfsStreamDirectory('bar');
        $this->abstractVisitor->expects($this->once())
                              ->method('visitDirectory')
                              ->with($this->equalTo($dir));
        $this->assertSame($this->abstractVisitor,
                          $this->abstractVisitor->visit($dir)
        );
    }
}
?>
