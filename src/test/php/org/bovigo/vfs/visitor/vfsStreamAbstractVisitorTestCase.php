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
use bovigo\callmap\NewInstance;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStreamBlock;
use PHPUnit\Framework\TestCase;

use function bovigo\callmap\verify;
/**
 * Test for org\bovigo\vfs\visitor\vfsStreamAbstractVisitor.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 * @group  issue_10
 */
class vfsStreamAbstractVisitorTestCase extends TestCase
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
        $this->abstractVisitor = NewInstance::of(vfsStreamAbstractVisitor::class);
    }

    /**
     * @test
     * @expectedException  \InvalidArgumentException
     */
    public function visitThrowsInvalidArgumentExceptionOnUnknownContentType()
    {
        $content = NewInstance::of(vfsStreamContent::class)->returns([
            'getType' => 'invalid'
        ]);
        $this->abstractVisitor->visit($content);
    }

    /**
     * @test
     */
    public function visitWithFileCallsVisitFile()
    {
        $file = new vfsStreamFile('foo.txt');
        $this->assertSame(
            $this->abstractVisitor,
            $this->abstractVisitor->visit($file)
        );
        verify($this->abstractVisitor, 'visitFile')->received($file);
    }

    /**
     * tests that a block device eventually calls out to visit file
     *
     * @test
     */
    public function visitWithBlockCallsVisitFile()
    {
        $block = new vfsStreamBlock('foo');
        $this->assertSame(
            $this->abstractVisitor,
            $this->abstractVisitor->visit($block)
        );
        verify($this->abstractVisitor, 'visitFile')->received($block);
    }

    /**
     * @test
     */
    public function visitWithDirectoryCallsVisitDirectory()
    {
        $dir = new vfsStreamDirectory('bar');
        $this->assertSame(
            $this->abstractVisitor,
            $this->abstractVisitor->visit($dir)
        );
        verify($this->abstractVisitor, 'visitDirectory')->received($dir);
    }
}
