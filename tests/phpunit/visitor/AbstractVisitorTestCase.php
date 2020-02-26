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
use bovigo\vfs\BasicFile;
use bovigo\vfs\vfsBlock;
use bovigo\vfs\vfsDirectory;
use bovigo\vfs\vfsFile;
use bovigo\vfs\visitor\AbstractVisitor;
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
class AbstractVisitorTestCase extends TestCase
{
    /**
     * instance to test
     *
     * @var  AbstractVisitor
     */
    protected $abstractVisitor;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $this->abstractVisitor = NewInstance::of(AbstractVisitor::class);
    }

    /**
     * @test
     */
    public function visitThrowsInvalidArgumentExceptionOnUnknownContentType(): void
    {
        $content = NewInstance::stub(BasicFile::class)->returns([
            'name' => 'foo.txt',
            'type' => -1,
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
        $file = new vfsFile('foo.txt');
        $this->abstractVisitor->visit($file);
        verify($this->abstractVisitor, 'visitFile')->received($file);
    }

    /**
     * @test
     */
    public function visitWithBlockEventuallyCallsVisitFile(): void
    {
        $block = new vfsBlock('foo');
        $this->abstractVisitor->visit($block);
        verify($this->abstractVisitor, 'visitFile')->received($block);
    }

    /**
     * @test
     */
    public function visitWithDirectoryCallsVisitDirectory(): void
    {
        $dir = new vfsDirectory('bar');
        $this->abstractVisitor->visit($dir);
        verify($this->abstractVisitor, 'visitDirectory')->received($dir);
    }
}
