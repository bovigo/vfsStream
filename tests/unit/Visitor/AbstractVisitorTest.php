<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  o Vfs
 */

use Vfs\Directory as vfsStreamDirectory;
use Vfs\File as vfsStreamFile;

/**
 * Test for org\bovigo\vfs\visitor\vfsStreamAbstractVisitor.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 * @group  issue_10
 */
class AbstractVisitorTest extends \PHPUnit_Framework_TestCase
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
        $this->abstractVisitor = $this->getMock('Vfs\Visitor\AbstractVisitor', array('visitFile', 'visitDirectory'));
    }

    /**
     * @test
     * @expectedException  \InvalidArgumentException
     */
    public function visitThrowsInvalidArgumentExceptionOnUnknownContentType()
    {
        $mockContent = $this->getMock('Vfs\Content');
        $mockContent->expects($this->any())
                    ->method('getType')
                    ->will($this->returnValue('invalid'));

        $this->assertSame($this->abstractVisitor, $this->abstractVisitor->visit($mockContent));
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

        $this->assertSame($this->abstractVisitor, $this->abstractVisitor->visit($file));
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

        $this->assertSame($this->abstractVisitor, $this->abstractVisitor->visit($dir));
    }
}
