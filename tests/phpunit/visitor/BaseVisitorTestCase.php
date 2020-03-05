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

use bovigo\callmap\NewInstance;
use bovigo\vfs\vfsBlock;
use bovigo\vfs\vfsDirectory;
use bovigo\vfs\vfsFile;
use bovigo\vfs\vfsStreamContent;
use bovigo\vfs\visitor\BaseVisitor;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\expect;
use function bovigo\callmap\verify;

/**
 * Test for bovigo\vfs\visitor\BaseVisitor.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 * @group  issue_10
 */
class BaseVisitorTestCase extends \BC_PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @var  BaseVisitor
     */
    protected $baseVisitor;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->baseVisitor = $this->bc_getMock('bovigo\\vfs\\visitor\\BaseVisitor',
                                                array('visitFile', 'visitDirectory')
                                 );
    }

    /**
     * @test
     * @expectedException  \InvalidArgumentException
     */
    public function visitThrowsInvalidArgumentExceptionOnUnknownContentType()
    {
        $mockContent = $this->bc_getMock('org\\bovigo\\vfs\\vfsStreamContent');
        $mockContent->expects($this->any())
                    ->method('getType')
                    ->will($this->returnValue('invalid'));
        $this->assertSame($this->baseVisitor,
                          $this->baseVisitor->visit($mockContent)
        );
    }

    /**
     * @test
     */
    public function visitWithFileCallsVisitFile()
    {
        $file = new vfsFile('foo.txt');
        $this->baseVisitor->expects($this->once())
                              ->method('visitFile')
                              ->with($this->equalTo($file));
        $this->assertSame($this->baseVisitor,
                          $this->baseVisitor->visit($file)
        );
    }

    /**
     * tests that a block device eventually calls out to visit file
     *
     * @test
     */
    public function visitWithBlockCallsVisitFile()
    {
        $block = new vfsBlock('foo');
        $this->baseVisitor->expects($this->once())
                              ->method('visitFile')
                              ->with($this->equalTo($block));
        $this->assertSame($this->baseVisitor,
                          $this->baseVisitor->visit($block)
        );
    }

    /**
     * @test
     */
    public function visitWithDirectoryCallsVisitDirectory()
    {
        $dir = new vfsDirectory('bar');
        $this->baseVisitor->expects($this->once())
                              ->method('visitDirectory')
                              ->with($this->equalTo($dir));
        $this->assertSame($this->baseVisitor,
                          $this->baseVisitor->visit($dir)
        );
    }
}
