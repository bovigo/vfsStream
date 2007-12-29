<?php
/**
 * Test runner for stubbles_vfs.
 *
 * @author      Frank Kleine <mikey@stubbles.net>
 * @package     stubbles_vfs
 * @subpackage  test
 */
ob_start();
define('SRC_PATH', realpath(dirname(__FILE__) . '/../../../../../'));
require_once SRC_PATH . '/main/php/org/simpletest/unit_tester.php';
require_once SRC_PATH . '/main/php/org/simpletest/mock_objects.php';
require_once SRC_PATH . '/main/php/org/simpletest/reporter.php';
/**
 * Test runner for stubbles_vfs.
 *
 * @package     stubbles_vfs
 * @subpackage  test
 */
class stubblesVfsTestRunner
{
    public function main()
    {
        $testFilePath = dirname(__FILE__);
        $testSuite = new TestSuite('All tests.');
        $testSuite->addTestFile($testFilePath . '/vfsStreamFileTestCase.php');
        $testSuite->addTestFile($testFilePath . '/vfsStreamDirectoryTestCase.php');
        $testSuite->addTestFile($testFilePath . '/vfsStreamTestCase.php');
        $testSuite->addTestFile($testFilePath . '/vfsStreamWrapperTestCase.php');
        if (PHP_SAPI == 'cli') {
            $reporter = new TextReporter();
        } else {
            $reporter = new HtmlReporter();
        }
        $testSuite->run($reporter);
    }
}
stubblesVfsTestRunner::main();
ob_end_flush();
?>