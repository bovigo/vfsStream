<?php
declare(strict_types=1);
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
use org\bovigo\vfs\vfsStreamContent;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStreamBlock;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\expect;
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
     */
    public function visitThrowsInvalidArgumentExceptionOnUnknownContentType()
    {
        $content = NewInstance::of(vfsStreamContent::class)->returns([
            'getName' => 'foo.txt',
            'getType' => -1
        ]);
        expect(function() use ($content) { $this->abstractVisitor->visit($content); })
          ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function visitWithFileCallsVisitFile()
    {
        $file = new vfsStreamFile('foo.txt');
        $this->abstractVisitor->visit($file);
        verify($this->abstractVisitor, 'visitFile')->received($file);
    }

    /**
     * @test
     */
    public function visitWithBlockEventuallyCallsVisitFile()
    {
        $block = new vfsStreamBlock('foo');
        $this->abstractVisitor->visit($block);
        verify($this->abstractVisitor, 'visitFile')->received($block);
    }

    /**
     * @test
     */
    public function visitWithDirectoryCallsVisitDirectory()
    {
        $dir = new vfsStreamDirectory('bar');
        $this->abstractVisitor->visit($dir);
        verify($this->abstractVisitor, 'visitDirectory')->received($dir);
    }
}
