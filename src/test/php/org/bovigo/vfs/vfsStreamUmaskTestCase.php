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
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assert;
use function bovigo\assert\predicate\equals;
/**
 * Test for umask settings.
 *
 * @group  permissions
 * @group  umask
 * @since  0.8.0
 */
class vfsStreamUmaskTestCase extends TestCase
{
    public function setUp()
    {
        vfsStream::umask(0000);
    }

    public function tearDown()
    {
        vfsStream::umask(0000);
    }

    /**
     * @test
     */
    public function gettingUmaskSettingDoesNotChangeUmaskSetting()
    {
        assert(vfsStream::umask(), equals(0000));
    }

    /**
     * @test
     */
    public function changingUmaskSettingReturnsOldUmaskSetting()
    {
        assert(vfsStream::umask(0022), equals(0000));
    }

    /**
     * @test
     */
    public function createFileWithDefaultUmaskSetting()
    {
        $file = vfsStream::newFile('foo');
        assert($file->getPermissions(), equals(0666));
    }

    /**
     * @test
     */
    public function createFileWithDifferentUmaskSetting()
    {
        vfsStream::umask(0022);
        $file = vfsStream::newFile('foo');
        assert($file->getPermissions(), equals(0644));
    }

    /**
     * @test
     */
    public function createDirectoryWithDefaultUmaskSetting()
    {
        $directory = vfsStream::newDirectory('foo');
        assert($directory->getPermissions(), equals(0777));
    }

    /**
     * @test
     */
    public function createDirectoryWithDifferentUmaskSetting()
    {
        vfsStream::umask(0022);
        $directory = vfsStream::newDirectory('foo');
        assert($directory->getPermissions(), equals(0755));
    }

    /**
     * @test
     */
    public function createFileUsingStreamWithDefaultUmaskSetting()
    {
        $root = vfsStream::setup();
        file_put_contents(vfsStream::url('root/newfile.txt'), 'file content');
        assert($root->getChild('newfile.txt')->getPermissions(), equals(0666));
    }

    /**
     * @test
     */
    public function createFileUsingStreamWithDifferentUmaskSetting()
    {
        $root = vfsStream::setup();
        vfsStream::umask(0022);
        file_put_contents(vfsStream::url('root/newfile.txt'), 'file content');
        assert($root->getChild('newfile.txt')->getPermissions(), equals(0644));
    }

    /**
     * @test
     */
    public function createDirectoryUsingStreamWithDefaultUmaskSetting()
    {
        $root = vfsStream::setup();
        mkdir(vfsStream::url('root/newdir'));
        assert($root->getChild('newdir')->getPermissions(), equals(0777));
    }

    /**
     * @test
     */
    public function createDirectoryUsingStreamWithDifferentUmaskSetting()
    {
        $root = vfsStream::setup();
        vfsStream::umask(0022);
        mkdir(vfsStream::url('root/newdir'));
        assert($root->getChild('newdir')->getPermissions(), equals(0755));
    }

    /**
     * @test
     */
    public function createDirectoryUsingStreamWithExplicit0()
    {
        $root = vfsStream::setup();
        vfsStream::umask(0022);
        mkdir(vfsStream::url('root/newdir'), 0000);
        assert($root->getChild('newdir')->getPermissions(), equals(0000));
    }

    /**
     * @test
     *
     */
    public function createDirectoryUsingStreamWithDifferentUmaskSettingButExplicit0777()
    {
        $root = vfsStream::setup();
        vfsStream::umask(0022);
        mkdir(vfsStream::url('root/newdir'), 0777);
        assert($root->getChild('newdir')->getPermissions(), equals(0755));
    }

    /**
     * @test
     */
    public function createDirectoryUsingStreamWithDifferentUmaskSettingButExplicitModeRequestedByCall()
    {
        $root = vfsStream::setup();
        vfsStream::umask(0022);
        mkdir(vfsStream::url('root/newdir'), 0700);
        assert($root->getChild('newdir')->getPermissions(), equals(0700));
    }

    /**
     * @test
     */
    public function defaultUmaskSettingDoesNotInfluenceSetup()
    {
        $root = vfsStream::setup();
        assert($root->getPermissions(), equals(0777));
    }

    /**
     * @test
     */
    public function umaskSettingShouldBeRespectedBySetup()
    {
        vfsStream::umask(0022);
        $root = vfsStream::setup();
        assert($root->getPermissions(), equals(0755));
    }
}
