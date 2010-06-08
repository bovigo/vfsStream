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
     * @test
     */
    public function directoryWritable()
    {
        vfsStream::setup('exampleDir');
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
        vfsStream::setup('exampleDir', 0444);
        $example = new FilePermissionsExample();
        $example->writeConfig(array('foo' => 'bar'),
                              vfsStream::url('exampleDir/notWritable.ini')
        );
    }
}
?>