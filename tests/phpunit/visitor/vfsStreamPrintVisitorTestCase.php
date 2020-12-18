<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests\visitor;

use bovigo\vfs\vfsStream;
use bovigo\vfs\visitor\vfsStreamPrintVisitor;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function file_get_contents;
use function fopen;
use function xml_parser_create;

/**
 * Test for bovigo\vfs\visitor\vfsStreamPrintVisitor.
 *
 * @see    https://github.com/mikey179/vfsStream/issues/10
 *
 * @since  0.10.0
 * @group  issue_10
 */
class vfsStreamPrintVisitorTestCase extends TestCase
{
    /**
     * @test
     */
    public function constructWithNonResourceThrowsInvalidArgumentException(): void
    {
        expect(static function (): void {
            new vfsStreamPrintVisitor('invalid');
        })
            ->throws(InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function constructWithNonStreamResourceThrowsInvalidArgumentException(): void
    {
        expect(static function (): void {
            new vfsStreamPrintVisitor(xml_parser_create());
        })
            ->throws(InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function visitFileWritesFileNameToStream(): void
    {
        $output = vfsStream::newFile('foo.txt')->at(vfsStream::setup());
        $printVisitor = new vfsStreamPrintVisitor(fopen('vfs://root/foo.txt', 'wb'));
        $printVisitor->visitFile(vfsStream::newFile('bar.txt'));
        assertThat($output->getContent(), equals("- bar.txt\n"));
    }

    /**
     * @test
     */
    public function visitFileWritesBlockDeviceToStream(): void
    {
        $output = vfsStream::newFile('foo.txt')->at(vfsStream::setup());
        $printVisitor = new vfsStreamPrintVisitor(fopen('vfs://root/foo.txt', 'wb'));
        $printVisitor->visitBlockDevice(vfsStream::newBlock('bar'));
        assertThat($output->getContent(), equals("- [bar]\n"));
    }

    /**
     * @test
     */
    public function visitDirectoryWritesDirectoryNameToStream(): void
    {
        $output = vfsStream::newFile('foo.txt')->at(vfsStream::setup());
        $printVisitor = new vfsStreamPrintVisitor(fopen('vfs://root/foo.txt', 'wb'));
        $printVisitor->visitDirectory(vfsStream::newDirectory('baz'));
        assertThat($output->getContent(), equals("- baz\n"));
    }

    /**
     * @test
     */
    public function visitRecursiveDirectoryStructure(): void
    {
        $root = vfsStream::setup(
            'root',
            null,
            [
                'test' => [
                    'foo' => ['test.txt' => 'hello'],
                    'baz.txt' => 'world',
                ],
                'foo.txt' => '',
            ]
        );
        $printVisitor = new vfsStreamPrintVisitor(fopen('vfs://root/foo.txt', 'wb'));
        $printVisitor->visitDirectory($root);
        assertThat(
            file_get_contents('vfs://root/foo.txt'),
            equals("- root\n  - test\n    - foo\n      - test.txt\n    - baz.txt\n  - foo.txt\n")
        );
    }
}
