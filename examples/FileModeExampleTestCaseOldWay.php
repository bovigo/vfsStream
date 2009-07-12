<?php
/**
 * Test case for class FilemodeExample.
 *
 * @package     stubbles_vfs
 * @subpackage  examples
 * @version     $Id$
 */
require_once 'PHPUnit/Framework.php';
require_once 'FilemodeExample.php';
/**
 * Test case for class FilemodeExample.
 *
 * @package     stubbles_vfs
 * @subpackage  examples
 */
class FileModeExampleTestCaseOldWay extends PHPUnit_Framework_TestCase
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
     * test correct file mode for created directory
     */
    public function testDirectoryHasCorrectDefaultFilePermissions()
    {
        $example = new FilemodeExample('id');
        $example->setDirectory(dirname(__FILE__));
        if (DIRECTORY_SEPARATOR === '\\') {
            // can not really test on windows, filemode from mkdir() is ignored
            $this->assertEquals(40777, decoct(fileperms(dirname(__FILE__) . '/id')));
        } else {
            $this->assertEquals(40700, decoct(fileperms(dirname(__FILE__) . '/id')));
        }
    }

    /**
     * test correct file mode for created directory
     */
    public function testDirectoryHasCorrectDifferentFilePermissions()
    {
        $example = new FilemodeExample('id', 0755);
        $example->setDirectory(dirname(__FILE__));
        if (DIRECTORY_SEPARATOR === '\\') {
            // can not really test on windows, filemode from mkdir() is ignored
            $this->assertEquals(40777, decoct(fileperms(dirname(__FILE__) . '/id')));
        } else {
            $this->assertEquals(40755, decoct(fileperms(dirname(__FILE__) . '/id')));
        }
    }
}
?>