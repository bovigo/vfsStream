<?php
/**
 * script to automate the generation of the
 * package.xml file.
 *
 * @author      Frank Kleine <mikey@stubbles.net>
 * @package     stubbles_vfs
 * @subpackage  build
 */

/**
 * uses PackageFileManager
 */
require_once 'PEAR/PackageFileManager2.php';
require_once 'PEAR/PackageFileManager/Svn.php';

/**
 * current version
 */
$version = '0.2.0';

/**
 * Current API version
 */
$apiVersion = '0.2.0';

/**
 * current state
 */
$state = 'alpha';

/**
 * current API stability
 */
$apiStability = 'alpha';

/**
 * release notes
 */
$notes = utf8_encode(trim('
- moved vfsStreamWrapper::PROTOCOL to vfsStream::SCHEME
- added new vfsStream::url() method to assist in creating correct vfsStream urls
- added vfsStream::path() method as opposite to vfsStream::url()
- a call to vfsStreamWrapper::register() will now reset the root to null (implemented because of a hint by David Zülke)
- added support for is_readable(), is_dir(), is_file()
- added vfsStream::newFile() to be able to do $file = vfsStream::newFile("foo.txt")->withContent("bar");
'));

/**
 * package description
 */
$description = <<<EOT
vfsStream is a stream wrapper for a virtual file system that may be helpful
in unit tests to mock the real file system.
EOT;

$package = new PEAR_PackageFileManager2();

$result = $package->setOptions(array(
    'filelistgenerator' => 'file',
    'ignore'            => array('package.php', 'package.xml', 'rfcs'),
    'simpleoutput'      => true,
    'baseinstalldir'    => '/',
    'packagedirectory'  => './',
    'dir_roles'         => array('examples' => 'doc')
    ));
if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}

$package->setPackage('vfsStream');
$package->setSummary('Mock file system calls.');
$package->setDescription($description);

$package->setChannel('pear.php-tools.net');
$package->setAPIVersion($apiVersion);
$package->setReleaseVersion($version);
$package->setReleaseStability($state);
$package->setAPIStability($apiStability);
$package->setNotes($notes);
$package->setPackageType('php');
$package->setLicense('BSD', 'http://www.opensource.org/licenses/bsd-license.php');

$package->addMaintainer('lead', 'mikey', 'Frank Kleine', 'mikey@stubbles.net', 'yes');

$package->setPhpDep('5.2.0');
$package->setPearinstallerDep('1.4.0');

$package->generateContents();

if (isset($_GET['make']) || (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'make')) {
    $result = $package->writePackageFile();
} else {
    $result = $package->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
?>