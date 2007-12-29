<?php
/**
 * Class to organize all tests.
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo
 * @subpackage  test
 */
if (defined('PHPUnit_MAIN_METHOD') === false) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}
require_once dirname(__FILE__) . '/init.php';
require_once dirname(__FILE__) . '/vfsTestSuite.php';
/**
 * Class to organize all tests.
 *
 * @package     bovigo
 * @subpackage  test
 */
class AllTests
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
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTest(vfsTestSuite::suite());
        return $suite;
    }
}
 
if (PHPUnit_MAIN_METHOD === 'AllTests::main') {
    AllTests::main();
}
?>