<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests;

use bovigo\callmap\NewInstance;
use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamAbstractContent;
use bovigo\vfs\vfsStreamContent;
use bovigo\vfs\vfsStreamException;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertFalse;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;

/**
 * Test for bovigo\vfs\vfsStreamAbstractContent.
 */
class vfsStreamAbstractContentTestCase extends TestCase
{
    private const OTHER = -1;

    private function createContent(int $permissions): vfsStreamContent
    {
        return NewInstance::of(vfsStreamAbstractContent::class, ['foo', $permissions])
            ->returns([
                'getDefaultPermissions' => 0777,
                'size' => 0,
            ]);
    }

    /**
     * @test
     */
    public function invalidCharacterInNameThrowsException(): void
    {
        expect(static function (): void {
            NewInstance::of(vfsStreamAbstractContent::class, ['foo/bar']);
        })->throws(vfsStreamException::class);
    }

    /**
     * @test
     * @group  permissions
     * @group  bug_15
     */
    public function noPermissionsForEveryone(): void
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
    public function executePermissionsForUser(): void
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
    public function executePermissionsForGroup(): void
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
    public function executePermissionsForOther(): void
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
    public function writePermissionsForUser(): void
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
    public function writePermissionsForGroup(): void
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
    public function writePermissionsForOther(): void
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
    public function executeAndWritePermissionsForUser(): void
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
    public function executeAndWritePermissionsForGroup(): void
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
    public function executeAndWritePermissionsForOther(): void
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
    public function readPermissionsForUser(): void
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
    public function readPermissionsForGroup(): void
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
    public function readPermissionsForOther(): void
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
    public function readAndExecutePermissionsForUser(): void
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
    public function readAndExecutePermissionsForGroup(): void
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
    public function readAndExecutePermissionsForOther(): void
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
    public function readAndWritePermissionsForUser(): void
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
    public function readAndWritePermissionsForGroup(): void
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
    public function readAndWritePermissionsForOther(): void
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
    public function allPermissionsForUser(): void
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
    public function allPermissionsForGroup(): void
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
    public function allPermissionsForOther(): void
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

    /**
     * @test
     */
    public function canBeRenamed(): void
    {
        $content = $this->createContent(0600);
        $content->rename('bar');
        assertThat($content->getName(), equals('bar'));
        assertFalse($content->appliesTo('foo'));
        assertFalse($content->appliesTo('foo/bar'));
        assertTrue($content->appliesTo('bar'));
    }

    /**
     * @test
     */
    public function renameToInvalidNameThrowsException(): void
    {
        $content = $this->createContent(0600);
        expect(static function () use ($content): void {
            $content->rename('foo/baz');
        })->throws(vfsStreamException::class);
    }
}
