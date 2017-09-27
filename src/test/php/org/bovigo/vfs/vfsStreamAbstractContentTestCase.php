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
namespace org\bovigo\vfs;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
/**
 * Test for org\bovigo\vfs\vfsStreamAbstractContent.
 */
class vfsStreamAbstractContentTestCase extends TestCase
{
    private const OTHER = -1;

    private function createContent($permissions): vfsStreamContent
    {
        return NewInstance::of(vfsStreamAbstractContent::class, ['foo', $permissions])
            ->returns([
                'getDefaultPermissions' => 0777,
                'size'                  => 0
            ]);
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function noPermissionsForEveryone()
    {
        $content = $this->createContent(0000);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function executePermissionsForUser()
    {
        $content = $this->createContent(0100);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
           vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertTrue($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function executePermissionsForGroup()
    {
        $content = $this->createContent(0010);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertTrue($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function executePermissionsForOther()
    {
        $content = $this->createContent(0001);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertTrue($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function writePermissionsForUser()
    {
        $content = $this->createContent(0200);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertTrue($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function writePermissionsForGroup()
    {
        $content = $this->createContent(0020);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertTrue($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function writePermissionsForOther()
    {
        $content = $this->createContent(0002);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertTrue($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function executeAndWritePermissionsForUser()
    {
        $content = $this->createContent(0300);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertTrue($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertTrue($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function executeAndWritePermissionsForGroup()
    {
        $content = $this->createContent(0030);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertTrue($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertTrue($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function executeAndWritePermissionsForOther()
    {
        $content = $this->createContent(0003);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertTrue($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertTrue($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readPermissionsForUser()
    {
        $content = $this->createContent(0400);
        assertTrue($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readPermissionsForGroup()
    {
        $content = $this->createContent(0040);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertTrue($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readPermissionsForOther()
    {
        $content = $this->createContent(0004);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertTrue($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readAndExecutePermissionsForUser()
    {
        $content = $this->createContent(0500);
        assertTrue($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertTrue($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readAndExecutePermissionsForGroup()
    {
        $content = $this->createContent(0050);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertTrue($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertTrue($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readAndExecutePermissionsForOther()
    {
        $content = $this->createContent(0005);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertTrue($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertTrue($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readAndWritePermissionsForUser()
    {
        $content = $this->createContent(0600);
        assertTrue($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertTrue($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readAndWritePermissionsForGroup()
    {
        $content = $this->createContent(0060);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertTrue($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertTrue($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function readAndWritePermissionsForOther()
    {
        $content = $this->createContent(0006);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertTrue($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertTrue($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function allPermissionsForUser()
    {
        $content = $this->createContent(0700);
        assertTrue($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertTrue($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertTrue($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function allPermissionsForGroup()
    {
        $content = $this->createContent(0070);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertTrue($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertTrue($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertTrue($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertFalse($content->isExecutable(self::OTHER, self::OTHER));
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function allPermissionsForOther()
    {
        $content = $this->createContent(0007);
        assertFalse($content->isReadable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isReadable(self::OTHER, vfsStream::getCurrentGroup()));
        assertTrue($content->isReadable(self::OTHER, self::OTHER));

        assertFalse($content->isWritable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isWritable(self::OTHER, vfsStream::getCurrentGroup()));
        assertTrue($content->isWritable(self::OTHER, self::OTHER));

        assertFalse($content->isExecutable(
            vfsStream::getCurrentUser(),
            vfsStream::getCurrentGroup()
        ));
        assertFalse($content->isExecutable(self::OTHER, vfsStream::getCurrentGroup()));
        assertTrue($content->isExecutable(self::OTHER, self::OTHER));
    }
}
