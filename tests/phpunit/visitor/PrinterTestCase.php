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

use bovigo\vfs\vfsStream;
use bovigo\vfs\visitor\Printer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function file_get_contents;
use function fopen;
use function xml_parser_create;

/**
 * Test for bovigo\vfs\visitor\Printer.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 * @group  issue_10
 */
class PrinterTestCase extends \BC_PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException  \InvalidArgumentException
     */
    public function constructWithNonResourceThrowsInvalidArgumentException()
    {
        new Printer('invalid');
    }

    /**
     * @test
     * @expectedException  \InvalidArgumentException
     */
    public function constructWithNonStreamResourceThrowsInvalidArgumentException()
    {
        new Printer(xml_parser_create());
    }

    /**
     * @test
     */
    public function visitFileWritesFileNameToStream()
    {
        $output       = vfsStream::newFile('foo.txt')
                                       ->at(vfsStream::setup());
        $printer = new Printer(fopen('vfs://root/foo.txt', 'wb'));
        $this->assertSame($printer,
                          $printer->visitFile(vfsStream::newFile('bar.txt'))
        );
        $this->assertEquals("- bar.txt\n", $output->getContent());
    }

    /**
     * @test
     */
    public function visitFileWritesBlockDeviceToStream()
    {
        $output       = vfsStream::newFile('foo.txt')
                                       ->at(vfsStream::setup());
        $printer = new Printer(fopen('vfs://root/foo.txt', 'wb'));
        $this->assertSame($printer,
                          $printer->visitBlockDevice(vfsStream::newBlock('bar'))
        );
        $this->assertEquals("- [bar]\n", $output->getContent());
    }

    /**
     * @test
     */
    public function visitDirectoryWritesDirectoryNameToStream()
    {
        $output       = vfsStream::newFile('foo.txt')
                                       ->at(vfsStream::setup());
        $printer = new Printer(fopen('vfs://root/foo.txt', 'wb'));
        $this->assertSame($printer,
                          $printer->visitDirectory(vfsStream::newDirectory('baz'))
        );
        $this->assertEquals("- baz\n", $output->getContent());
    }

    /**
     * @test
     */
    public function visitRecursiveDirectoryStructure()
    {
        $root         = vfsStream::setup('root',
                                         null,
                                         array('test' => array('foo'     => array('test.txt' => 'hello'),
                                                               'baz.txt' => 'world'
                                                           ),
                                               'foo.txt' => ''
                                         )
                        );
        $printer = new Printer(fopen('vfs://root/foo.txt', 'wb'));
        $this->assertSame($printer,
                          $printer->visitDirectory($root)
        );
        $this->assertEquals("- root\n  - test\n    - foo\n      - test.txt\n    - baz.txt\n  - foo.txt\n", file_get_contents('vfs://root/foo.txt'));
    }
}
