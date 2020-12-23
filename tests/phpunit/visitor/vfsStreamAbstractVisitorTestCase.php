<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests\visitor;

use bovigo\callmap\NewInstance;
use bovigo\vfs\vfsStreamBlock;
use bovigo\vfs\vfsStreamContent;
use bovigo\vfs\vfsStreamDirectory;
use bovigo\vfs\vfsStreamFile;
use bovigo\vfs\visitor\vfsStreamAbstractVisitor;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\expect;
use function bovigo\callmap\verify;

/**
 * Test for bovigo\vfs\visitor\vfsStreamAbstractVisitor.
 *
 * @see    https://github.com/mikey179/vfsStream/issues/10
 *
 * @since  0.10.0
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
    protected function setUp(): void
    {
        $this->abstractVisitor = NewInstance::of(vfsStreamAbstractVisitor::class);
    }

    /**
     * @test
     */
    public function visitThrowsInvalidArgumentExceptionOnUnknownContentType(): void
    {
        $content = NewInstance::of(vfsStreamContent::class)->returns([
            'getName' => 'foo.txt',
            'getType' => -1,
        ]);
        expect(function () use ($content): void {
            $this->abstractVisitor->visit($content);
        })
          ->throws(InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function visitWithFileCallsVisitFile(): void
    {
        $file = new vfsStreamFile('foo.txt');
        $this->abstractVisitor->visit($file);
        verify($this->abstractVisitor, 'visitFile')->received($file);
    }

    /**
     * @test
     */
    public function visitWithBlockEventuallyCallsVisitFile(): void
    {
        $block = new vfsStreamBlock('foo');
        $this->abstractVisitor->visit($block);
        verify($this->abstractVisitor, 'visitFile')->received($block);
    }

    /**
     * @test
     */
    public function visitWithDirectoryCallsVisitDirectory(): void
    {
        $dir = new vfsStreamDirectory('bar');
        $this->abstractVisitor->visit($dir);
        verify($this->abstractVisitor, 'visitDirectory')->received($dir);
    }
}
