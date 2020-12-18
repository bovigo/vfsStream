<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
use function file_put_contents;
use function mkdir;

/**
 * Test for umask settings.
 *
 * @group  permissions
 * @group  umask
 * @since  0.8.0
 */
class vfsStreamUmaskTestCase extends TestCase
{
    protected function setUp(): void
    {
        vfsStream::umask(0000);
    }

    protected function tearDown(): void
    {
        vfsStream::umask(0000);
    }

    /**
     * @test
     */
    public function gettingUmaskSettingDoesNotChangeUmaskSetting(): void
    {
        assertThat(vfsStream::umask(), equals(0000));
    }

    /**
     * @test
     */
    public function changingUmaskSettingReturnsOldUmaskSetting(): void
    {
        assertThat(vfsStream::umask(0022), equals(0000));
    }

    /**
     * @test
     */
    public function createFileWithDefaultUmaskSetting(): void
    {
        $file = vfsStream::newFile('foo');
        assertThat($file->getPermissions(), equals(0666));
    }

    /**
     * @test
     */
    public function createFileWithDifferentUmaskSetting(): void
    {
        vfsStream::umask(0022);
        $file = vfsStream::newFile('foo');
        assertThat($file->getPermissions(), equals(0644));
    }

    /**
     * @test
     */
    public function createDirectoryWithDefaultUmaskSetting(): void
    {
        $directory = vfsStream::newDirectory('foo');
        assertThat($directory->getPermissions(), equals(0777));
    }

    /**
     * @test
     */
    public function createDirectoryWithDifferentUmaskSetting(): void
    {
        vfsStream::umask(0022);
        $directory = vfsStream::newDirectory('foo');
        assertThat($directory->getPermissions(), equals(0755));
    }

    /**
     * @test
     */
    public function createFileUsingStreamWithDefaultUmaskSetting(): void
    {
        $root = vfsStream::setup();
        file_put_contents(vfsStream::url('root/newfile.txt'), 'file content');
        assertThat($root->getChild('newfile.txt')->getPermissions(), equals(0666));
    }

    /**
     * @test
     */
    public function createFileUsingStreamWithDifferentUmaskSetting(): void
    {
        $root = vfsStream::setup();
        vfsStream::umask(0022);
        file_put_contents(vfsStream::url('root/newfile.txt'), 'file content');
        assertThat($root->getChild('newfile.txt')->getPermissions(), equals(0644));
    }

    /**
     * @test
     */
    public function createDirectoryUsingStreamWithDefaultUmaskSetting(): void
    {
        $root = vfsStream::setup();
        mkdir(vfsStream::url('root/newdir'));
        assertThat($root->getChild('newdir')->getPermissions(), equals(0777));
    }

    /**
     * @test
     */
    public function createDirectoryUsingStreamWithDifferentUmaskSetting(): void
    {
        $root = vfsStream::setup();
        vfsStream::umask(0022);
        mkdir(vfsStream::url('root/newdir'));
        assertThat($root->getChild('newdir')->getPermissions(), equals(0755));
    }

    /**
     * @test
     */
    public function createDirectoryUsingStreamWithExplicit0(): void
    {
        $root = vfsStream::setup();
        vfsStream::umask(0022);
        mkdir(vfsStream::url('root/newdir'), 0000);
        assertThat($root->getChild('newdir')->getPermissions(), equals(0000));
    }

    /**
     * @test
     */
    public function createDirectoryUsingStreamWithDifferentUmaskSettingButExplicit0777(): void
    {
        $root = vfsStream::setup();
        vfsStream::umask(0022);
        mkdir(vfsStream::url('root/newdir'), 0777);
        assertThat($root->getChild('newdir')->getPermissions(), equals(0755));
    }

    /**
     * @test
     */
    public function createDirectoryUsingStreamWithDifferentUmaskSettingButExplicitModeRequestedByCall(): void
    {
        $root = vfsStream::setup();
        vfsStream::umask(0022);
        mkdir(vfsStream::url('root/newdir'), 0700);
        assertThat($root->getChild('newdir')->getPermissions(), equals(0700));
    }

    /**
     * @test
     */
    public function defaultUmaskSettingDoesNotInfluenceSetup(): void
    {
        $root = vfsStream::setup();
        assertThat($root->getPermissions(), equals(0777));
    }

    /**
     * @test
     */
    public function umaskSettingShouldBeRespectedBySetup(): void
    {
        vfsStream::umask(0022);
        $root = vfsStream::setup();
        assertThat($root->getPermissions(), equals(0755));
    }
}
