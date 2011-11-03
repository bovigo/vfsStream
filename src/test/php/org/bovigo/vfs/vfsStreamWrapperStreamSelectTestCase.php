<?php
/**
 * Test for org::bovigo::vfs::vfsStreamWrapper.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
require_once 'org/bovigo/vfs/vfsStream.php';
require_once 'PHPUnit/Framework/TestCase.php';
/**
 * Test for org::bovigo::vfs::vfsStreamWrapper.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 * @since       0.9.0
 * @group       issue_3
 */
class vfsStreamWrapperSelectStreamTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException PHPUnit_Framework_Error
     */
    public function selectStream()
    {
        if (version_compare('5.3.0', PHP_VERSION, '>')) {
            $this->markTestSkipped('Test only applies to PHP 5.3.0 or greater.');
        }
        
        $root = vfsStream::setup();
        $file = vfsStream::newFile('foo.txt')->at($root)->withContent('testContent');

        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $readarray   = array($fp);
        $writearray  = array();
        $exceptarray = array();
        stream_select($readarray, $writearray, $exceptarray, 1);
    }
}
?>