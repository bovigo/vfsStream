<?php
/**
 * Class to organize all tests.
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo
 * @subpackage  test
 */
if (defined('PHPUnit_MAIN_METHOD') === false) {
    define('PHPUnit_MAIN_METHOD', 'src_test_AllTests::main');
}

define('SOURCE_DIR', realpath(dirname(__FILE__) . '/../main/php'));
ini_set('include_path', SOURCE_DIR . ';' . ini_get('include_path'));
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Filter.php';
PHPUnit_Util_Filter::addDirectoryToWhitelist(SOURCE_DIR);
/**
 * Class to organize all tests.
 *
 * @package     bovigo
 * @subpackage  test
 */
class src_test_AllTests extends PHPUnit_Framework_TestSuite
{
    /**
     * runs this test suite
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * returns the test suite to be run
     *
     * @return  PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite   = new self();
        $dirname = dirname(__FILE__);
        $suite->addTestFile($dirname . '/php/org/bovigo/vfs/vfsStreamDirectoryTestCase.php');
        $suite->addTestFile($dirname . '/php/org/bovigo/vfs/vfsStreamFileTestCase.php');
        $suite->addTestFile($dirname . '/php/org/bovigo/vfs/vfsStreamTestCase.php');
        $suite->addTestFile($dirname . '/php/org/bovigo/vfs/vfsStreamWrapperAlreadyRegisteredTestCase.php');
        $suite->addTestFile($dirname . '/php/org/bovigo/vfs/vfsStreamWrapperDirTestCase.php');
        $suite->addTestFile($dirname . '/php/org/bovigo/vfs/vfsStreamWrapperTestCase.php');
        $suite->addTestFile($dirname . '/php/org/bovigo/vfs/vfsStreamWrapperWithoutRootTestCase.php');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD === 'src_test_AllTests::main') {
    src_test_AllTests::main();
}
?>