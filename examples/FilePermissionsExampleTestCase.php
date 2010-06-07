<?php
/**
 * Test for FilePermissionsExample.
 *
 * @package     bovigo_vfs
 * @subpackage  examples
 * @version     $Id$
 */
require_once 'PHPUnit/Framework.php';
require_once 'vfsStream/vfsStream.php';
require_once 'FilePermissionsExample.php';
/**
 * Test for FilePermissionsExample.
 *
 * @package     bovigo_vfs
 * @subpackage  examples
 */
class FilePermissionsExampleTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * set up test environment
     */
    public function setUp()
    {
        vfsStreamWrapper::register();
    }

    /**
     * @test
     */
    public function directoryWritable()
    {
        vfsStreamWrapper::setRoot(vfsStream::newDirectory('exampleDir'));
        $example = new FilePermissionsExample();
        $example->writeConfig(array('foo' => 'bar'),
                              vfsStream::url('exampleDir/writable.ini')
        );

        // assertions here
    }

    /**
     * @test
     */
    public function directoryNotWritable()
    {
        vfsStreamWrapper::setRoot(vfsStream::newDirectory('exampleDir', 0444));
        $example = new FilePermissionsExample();
        $example->writeConfig(array('foo' => 'bar'),
                              vfsStream::url('exampleDir/notWritable.ini')
        );
    }
}
?>