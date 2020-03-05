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
use bovigo\vfs\visitor\BaseVisitor;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\expect;
use function bovigo\callmap\verify;

/**
 * Test for bovigo\vfs\visitor\BaseVisitor.
 *
 * @see    https://github.com/mikey179/vfsStream/issues/10
 *
 * @since  0.10.0
 * @group  issue_10
 */
class BaseVisitorTestCase extends TestCase
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
    protected function setUp(): void
    {
        $this->baseVisitor = NewInstance::of(BaseVisitor::class);
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
            $this->baseVisitor->visit($content);
        })
          ->throws(InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function visitWithFileCallsVisitFile(): void
    {
        $file = new vfsFile('foo.txt');
        $this->baseVisitor->visit($file);
        verify($this->baseVisitor, 'visitFile')->received($file);
    }

    /**
     * @test
     */
    public function visitWithBlockEventuallyCallsVisitFile(): void
    {
        $block = new vfsBlock('foo');
        $this->baseVisitor->visit($block);
        verify($this->baseVisitor, 'visitFile')->received($block);
    }

    /**
     * @test
     */
    public function visitWithDirectoryCallsVisitDirectory(): void
    {
        $dir = new vfsDirectory('bar');
        $this->baseVisitor->visit($dir);
        verify($this->baseVisitor, 'visitDirectory')->received($dir);
    }
}
