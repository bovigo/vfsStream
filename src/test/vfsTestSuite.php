<?php
/**
 * Class to organize all tests.
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo
 * @subpackage  test
 */
if (defined('PHPUnit_MAIN_METHOD') === false) {
    define('PHPUnit_MAIN_METHOD', 'vfsTestSuite::main');
}
require_once 'init.php';
/**
 * Class to organize all tests.
 *
 * @package     bovigo
 * @subpackage  test
 */
class vfsTestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * runs this test suite
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * returns an instance of this suite
     *
     * @return  ImpTestSuite
     */
    public static function suite()
    {
        $suite   = new self();
        $dirname = dirname(__FILE__);
        $suite->addTestFile($dirname . '/php/org/bovigo/vfs/vfsStreamDirectoryTestCase.php');
        $suite->addTestFile($dirname . '/php/org/bovigo/vfs/vfsStreamFileTestCase.php');
        $suite->addTestFile($dirname . '/php/org/bovigo/vfs/vfsStreamTestCase.php');
        $suite->addTestFile($dirname . '/php/org/bovigo/vfs/vfsStreamWrapperTestCase.php');
        $suite->addTestFile($dirname . '/php/org/bovigo/vfs/vfsStreamWrapperWithoutRootTestCase.php');
        return $suite;
    }
}
 
if (PHPUnit_MAIN_METHOD === 'vfsTestSuite::main') {
    ImpTestSuite::main();
}
?>