<?php
/**
 * Initialize test stuff.
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo
 * @subpackage  test
 */
define('SOURCE_DIR', realpath(dirname(__FILE__) . '/../main/php'));
ini_set('include_path', ini_get('include_path') . ';' . SOURCE_DIR);
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Filter.php';
PHPUnit_Util_Filter::addDirectoryToWhitelist(SOURCE_DIR);
?>