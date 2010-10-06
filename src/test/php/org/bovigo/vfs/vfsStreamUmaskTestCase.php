<?php
/**
 * Test for umask settings.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 * @version     $Id$
 */
require_once 'org/bovigo/vfs/vfsStream.php';
require_once 'org/bovigo/vfs/vfsStreamDirectory.php';
require_once 'org/bovigo/vfs/vfsStreamFile.php';
require_once 'PHPUnit/Framework.php';
/**
 * Test for umask settings.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 * @group       umask
 * @since       0.8.0
 */
class vfsStreamUmaskTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * set up test environment
     */
    public function setUp()
    {
        vfsStream::umask(0000);
    }

    /**
     * clean up test environment
     */
    public function tearDown()
    {
        vfsStream::umask(0000);
    }

    /**
     * @test
     */
    public function gettingUmaskSettingDoesNotChangeUmaskSetting()
    {
        $this->assertEquals(vfsStream::umask(),
                            vfsStream::umask()
        );
        $this->assertEquals(0000,
                            vfsStream::umask()
        );
    }

    /**
     * @test
     */
    public function changingUmaskSettingReturnsOldUmaskSetting()
    {
        $this->assertEquals(0000,
                            vfsStream::umask(0022)
        );
        $this->assertEquals(0022,
                            vfsStream::umask()
        );
    }

    /**
     * @test
     */
    public function createFileWithDefaultUmaskSetting()
    {
        $file = new vfsStreamFile('foo');
        $this->assertEquals(0666, $file->getPermissions());
    }

    /**
     * @test
     */
    public function createFileWithDifferentUmaskSetting()
    {
        vfsStream::umask(0022);
        $file = new vfsStreamFile('foo');
        $this->assertEquals(0644, $file->getPermissions());
    }

    /**
     * @test
     */
    public function createDirectoryWithDefaultUmaskSetting()
    {
        $directory = new vfsStreamDirectory('foo');
        $this->assertEquals(0777, $directory->getPermissions());
    }

    /**
     * @test
     */
    public function createDirectoryWithDifferentUmaskSetting()
    {
        vfsStream::umask(0022);
        $directory = new vfsStreamDirectory('foo');
        $this->assertEquals(0755, $directory->getPermissions());
    }

    /**
     * @test
     */
    public function createFileUsingStreamWithDefaultUmaskSetting()
    {
        $root = vfsStream::setup();
        file_put_contents(vfsStream::url('root/newfile.txt'), 'file content');
        $this->assertEquals(0666, $root->getChild('newfile.txt')->getPermissions());
    }

    /**
     * @test
     */
    public function createFileUsingStreamWithDifferentUmaskSetting()
    {
        $root = vfsStream::setup();
        vfsStream::umask(0022);
        file_put_contents(vfsStream::url('root/newfile.txt'), 'file content');
        $this->assertEquals(0644, $root->getChild('newfile.txt')->getPermissions());
    }

    /**
     * @test
     */
    public function createDirectoryUsingStreamWithDefaultUmaskSetting()
    {
        $root = vfsStream::setup();
        mkdir(vfsStream::url('root/newdir'));
        $this->assertEquals(0777, $root->getChild('newdir')->getPermissions());
    }

    /**
     * We can not differentiate whether 0777 was just the default value
     * because $mode was not set in the mkdir() function call or if if the
     * user explicitly called mkdir($newdir, 0777).
     * Therefore the vfsStream::umask() setting can not be applied to the new
     * directory. This in turn makes this test fail, but we don't want test
     * failures in our test result so we say it is an expected exception here.
     * In case this is fixed within PHP one day this test will fail with newer
     * PHP versions then and remember us to change it again.
     * As a workaround, the mkdir() function can be called with null as value
     * of the $mode param. See
     * createDirectoryUsingStreamWithDifferentUmaskSettingWorkaround()
     * for usage scenario.
     *
     * @test
     * @expectedException  PHPUnit_Framework_ExpectationFailedException
     */
    public function createDirectoryUsingStreamWithDifferentUmaskSetting()
    {
        $root = vfsStream::setup();
        vfsStream::umask(0022);
        mkdir(vfsStream::url('root/newdir'));
        $this->assertEquals(0755, $root->getChild('newdir')->getPermissions());
    }

    /**
     * @test
     */
    public function createDirectoryUsingStreamWithDifferentUmaskSettingWorkaround()
    {
        $root = vfsStream::setup();
        vfsStream::umask(0022);
        mkdir(vfsStream::url('root/newdir'), null);
        $this->assertEquals(0755, $root->getChild('newdir')->getPermissions());
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
        $this->assertEquals(0777, $root->getChild('newdir')->getPermissions());
    }

    /**
     * @test
     */
    public function createDirectoryUsingStreamWithDifferentUmaskSettingButExplicitModeRequestedByCall()
    {
        $root = vfsStream::setup();
        vfsStream::umask(0022);
        mkdir(vfsStream::url('root/newdir'), 0700);
        $this->assertEquals(0700, $root->getChild('newdir')->getPermissions());
    }

    /**
     * @test
     */
    public function defaultUmaskSettingDoesNotInfluenceSetup()
    {
        $root = vfsStream::setup();
        $this->assertEquals(0777, $root->getPermissions());
    }

    /**
     * @test
     */
    public function umaskSettingShouldBeRespectedBySetup()
    {
        vfsStream::umask(0022);
        $root = vfsStream::setup();
        $this->assertEquals(0755, $root->getPermissions());
    }
}
?>