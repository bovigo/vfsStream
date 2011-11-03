<?php
/**
 * Test for org::bovigo::vfs::vfsStream.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
require_once 'org/bovigo/vfs/vfsStream.php';
require_once 'PHPUnit/Framework/TestCase.php';
/**
 * Test for org::bovigo::vfs::vfsStream.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 * @since       0.9.0
 * @group       issue_2
 */
class vfsStreamGlobTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function globDoesNotWorkWithVfsStreamUrls()
    {
        $root = vfsStream::setup('example');
        mkdir(vfsStream::url('example/test/'), 0777, true);
        $this->assertEmpty(glob(vfsStream::url('example'), GLOB_MARK));
    }
}
?>