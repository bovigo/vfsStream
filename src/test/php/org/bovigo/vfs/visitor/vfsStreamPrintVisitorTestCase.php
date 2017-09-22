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
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assert;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Test for org\bovigo\vfs\visitor\vfsStreamPrintVisitor.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 * @group  issue_10
 */
class vfsStreamPrintVisitorTestCase extends TestCase
{
    /**
     * @test
     */
    public function constructWithNonResourceThrowsInvalidArgumentException()
    {
        expect(function() { new vfsStreamPrintVisitor('invalid'); })
            ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function constructWithNonStreamResourceThrowsInvalidArgumentException()
    {
        expect(function() { new vfsStreamPrintVisitor(xml_parser_create()); })
            ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function visitFileWritesFileNameToStream()
    {
        $output       = vfsStream::newFile('foo.txt')->at(vfsStream::setup());
        $printVisitor = new vfsStreamPrintVisitor(fopen('vfs://root/foo.txt', 'wb'));
        $printVisitor->visitFile(vfsStream::newFile('bar.txt'));
        assert($output->getContent(), equals("- bar.txt\n"));
    }

    /**
     * @test
     */
    public function visitFileWritesBlockDeviceToStream()
    {
        $output       = vfsStream::newFile('foo.txt')->at(vfsStream::setup());
        $printVisitor = new vfsStreamPrintVisitor(fopen('vfs://root/foo.txt', 'wb'));
        $printVisitor->visitBlockDevice(vfsStream::newBlock('bar'));
        assert($output->getContent(), equals("- [bar]\n"));
    }

    /**
     * @test
     */
    public function visitDirectoryWritesDirectoryNameToStream()
    {
        $output       = vfsStream::newFile('foo.txt')->at(vfsStream::setup());
        $printVisitor = new vfsStreamPrintVisitor(fopen('vfs://root/foo.txt', 'wb'));
        $printVisitor->visitDirectory(vfsStream::newDirectory('baz'));
        assert($output->getContent(), equals("- baz\n"));
    }

    /**
     * @test
     */
    public function visitRecursiveDirectoryStructure()
    {
        $root = vfsStream::setup(
            'root',
             null,
             ['test'    => ['foo'     => ['test.txt' => 'hello'],
                            'baz.txt' => 'world'
                           ],
              'foo.txt' => ''
             ]
        );
        $printVisitor = new vfsStreamPrintVisitor(fopen('vfs://root/foo.txt', 'wb'));
        $printVisitor->visitDirectory($root);
        assert(
            file_get_contents('vfs://root/foo.txt'),
            equals("- root\n  - test\n    - foo\n      - test.txt\n    - baz.txt\n  - foo.txt\n")
        );
    }
}
