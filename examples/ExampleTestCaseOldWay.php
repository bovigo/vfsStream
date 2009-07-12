<?php
/**
 * Test case for class Example.
 *
 * @package     bovigo_vfs
 * @subpackage  examples
 * @version     $Id$
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
}
?>