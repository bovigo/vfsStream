<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs\tests;

use bovigo\callmap\NewInstance;
use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamContent;
use bovigo\vfs\vfsDirectory;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isSameAs;
use function is_string;

/**
 * Test for bovigo\vfs\vfsDirectoryIterator.
 */
class vfsDirectoryIteratorTestCase extends \BC_PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  vfsDirectory
     */
    private $dir;
    /**
     * child one
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockChild1;
    /**
     * child two
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockChild2;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->dir = new vfsDirectory('foo');
        $this->mockChild1 = $this->bc_getMock('org\\bovigo\\vfs\\vfsStreamContent');
        $this->mockChild1->expects($this->any())
                         ->method('getName')
                         ->will($this->returnValue('bar'));
        $this->dir->addChild($this->mockChild1);
        $this->mockChild2 = $this->bc_getMock('org\\bovigo\\vfs\\vfsStreamContent');
        $this->mockChild2->expects($this->any())
                         ->method('getName')
                         ->will($this->returnValue('baz'));
        $this->dir->addChild($this->mockChild2);
    }

    /**
     * clean up test environment
     */
    public function tearDown()
    {
        vfsStream::enableDotfiles();
    }

    /**
     * @return  array
     */
    public function provideSwitchWithExpectations()
    {
        return array(array(function() { vfsStream::disableDotfiles(); },
                           array()
                     ),
                     array(function() { vfsStream::enableDotfiles(); },
                           array('.', '..')
                     )
        );
    }

    private function getDirName($dir)
    {
        if (is_string($dir)) {
            return $dir;
        }


        return $dir->getName();
    }

    /**
     * @param  \Closure  $dotFilesSwitch
     * @param  array     $dirNames
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function iteration(\Closure $dotFilesSwitch, array $dirs)
    {
        $dirs[] = $this->mockChild1;
        $dirs[] = $this->mockChild2;
        $dotFilesSwitch();
        $dirIterator = $this->dir->getIterator();
        foreach ($dirs as $dir) {
            $this->assertEquals($this->getDirName($dir), $dirIterator->key());
            $this->assertTrue($dirIterator->valid());
            if (!is_string($dir)) {
                $this->assertSame($dir, $dirIterator->current());
            }

            $dirIterator->next();
        }

        $this->assertFalse($dirIterator->valid());
        $this->assertNull($dirIterator->key());
        $this->assertNull($dirIterator->current());
    }
}
