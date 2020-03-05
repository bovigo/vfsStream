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
use bovigo\vfs\BasicFile;
use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamContent;
use bovigo\vfs\vfsStreamException;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;

/**
 * Test for bovigo\vfs\BasicFile.
 */
class TestBasicFile extends BasicFile
{
    /**
     * returns default permissions for concrete implementation
     *
     * @return  int
     * @since   0.8.0
     */
    protected function getDefaultPermissions()
    {
        return 0777;
    }

    /**
     * returns size of content
     *
     * @return  int
     */
    public function size()
    {
        return 0;
    }
}
/**
 * Test for org\bovigo\vfs\BasicFile.
 */
class BasicFileTestCase extends \BC_PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function noPermissionsForEveryone()
    {
        $abstractContent = new TestBasicFile('foo', 0000);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function executePermissionsForUser()
    {
        $abstractContent = new TestBasicFile('foo', 0100);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertTrue($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                         vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function executePermissionsForGroup()
    {
        $abstractContent = new TestBasicFile('foo', 0010);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isExecutable(-1,
                                                         vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function executePermissionsForOther()
    {
        $abstractContent = new TestBasicFile('foo', 0001);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isExecutable(-1,
                                                         -1
                                            )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function writePermissionsForUser()
    {
        $abstractContent = new TestBasicFile('foo', 0200);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertTrue($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                       vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function writePermissionsForGroup()
    {
        $abstractContent = new TestBasicFile('foo', 0020);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isWritable(-1,
                                                       vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function writePermissionsForOther()
    {
        $abstractContent = new TestBasicFile('foo', 0002);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isWritable(-1,
                                                       -1
                                            )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function executeAndWritePermissionsForUser()
    {
        $abstractContent = new TestBasicFile('foo', 0300);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertTrue($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                       vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertTrue($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                         vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function executeAndWritePermissionsForGroup()
    {
        $abstractContent = new TestBasicFile('foo', 0030);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isWritable(-1,
                                                       vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isExecutable(-1,
                                                         vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function executeAndWritePermissionsForOther()
    {
        $abstractContent = new TestBasicFile('foo', 0003);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isWritable(-1,
                                                       -1
                                            )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isExecutable(-1,
                                                         -1
                                            )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readPermissionsForUser()
    {
        $abstractContent = new TestBasicFile('foo', 0400);
        $this->assertTrue($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                       vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readPermissionsForGroup()
    {
        $abstractContent = new TestBasicFile('foo', 0040);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readPermissionsForOther()
    {
        $abstractContent = new TestBasicFile('foo', 0004);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isReadable(-1,
                                                       -1
                                            )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readAndExecutePermissionsForUser()
    {
        $abstractContent = new TestBasicFile('foo', 0500);
        $this->assertTrue($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                       vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertTrue($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                         vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readAndExecutePermissionsForGroup()
    {
        $abstractContent = new TestBasicFile('foo', 0050);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isExecutable(-1,
                                                         vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readAndExecutePermissionsForOther()
    {
        $abstractContent = new TestBasicFile('foo', 0005);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isReadable(-1,
                                                       -1
                                            )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isExecutable(-1,
                                                         -1
                                            )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readAndWritePermissionsForUser()
    {
        $abstractContent = new TestBasicFile('foo', 0600);
        $this->assertTrue($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                       vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertTrue($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                       vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readAndWritePermissionsForGroup()
    {
        $abstractContent = new TestBasicFile('foo', 0060);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isWritable(-1,
                                                       vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readAndWritePermissionsForOther()
    {
        $abstractContent = new TestBasicFile('foo', 0006);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isReadable(-1,
                                                       -1
                                            )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isWritable(-1,
                                                       -1
                                            )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function allPermissionsForUser()
    {
        $abstractContent = new TestBasicFile('foo', 0700);
        $this->assertTrue($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                       vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertTrue($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                       vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertTrue($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                         vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function allPermissionsForGroup()
    {
        $abstractContent = new TestBasicFile('foo', 0070);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isWritable(-1,
                                                       vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        -1
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isExecutable(-1,
                                                         vfsStream::getCurrentGroup()
                                            )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          -1
                                             )
               );
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function allPermissionsForOther()
    {
        $abstractContent = new TestBasicFile('foo', 0007);
        $this->assertFalse($abstractContent->isReadable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isReadable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isReadable(-1,
                                                       -1
                                            )
               );
        $this->assertFalse($abstractContent->isWritable(vfsStream::getCurrentUser(),
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isWritable(-1,
                                                        vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isWritable(-1,
                                                       -1
                                            )
               );
        $this->assertFalse($abstractContent->isExecutable(vfsStream::getCurrentUser(),
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertFalse($abstractContent->isExecutable(-1,
                                                          vfsStream::getCurrentGroup()
                                             )
               );
        $this->assertTrue($abstractContent->isExecutable(-1,
                                                         -1
                                            )
               );
    }
}
