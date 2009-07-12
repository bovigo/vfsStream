<?php
/**
 * Test case for class Example.
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo_vfs
 * @subpackage  examples
 */
require_once 'PHPUnit/Framework.php';
require_once 'Example.php';
/**
 * Test case for class Example.
 *
 * @package     bovigo_vfs
 * @subpackage  examples
 */
class ExampleTestCaseOldWay extends PHPUnit_Framework_TestCase
{
    /**
     * set up test environmemt
     */
    public function setUp()
    {
        if (file_exists(dirname(__FILE__) . '/id') === true) {
            rmdir(dirname(__FILE__) . '/id');
        }
    }

    /**
     * clear up test environment
     */
    public function tearDown()
    {
        if (file_exists(dirname(__FILE__) . '/id') === true) {
            rmdir(dirname(__FILE__) . '/id');
        }
    }

    /**
     * test that the directory is created
     */
    public function testDirectoryIsCreated()
    {
        $example = new Example('id');
        $this->assertFalse(file_exists(dirname(__FILE__) . '/id'));
        $example->setDirectory(dirname(__FILE__));
        $this->assertTrue(file_exists(dirname(__FILE__) . '/id'));
    }

    /**
     * test correct file mode for created directory
     */
    public function testDirectoryHasCorrectFilePermissions()
    {
        $example = new Example('id');
        $example->setDirectory(dirname(__FILE__));
        if (DIRECTORY_SEPARATOR === '\\') {
            // can not really test on windows, filemode from mkdir() is ignored
            $this->assertEquals(40777, decoct(fileperms(dirname(__FILE__) . '/id')));
        } else {
            $this->assertEquals(40700, decoct(fileperms(dirname(__FILE__) . '/id')));
        }
    }
}
?>