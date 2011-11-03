<?php
/**
 * Test for org::bovigo::vfs::vfsStreamWrapper in conjunction with ext/zip.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
require_once 'PHPUnit/Framework/TestCase.php';
/**
 * Test for org::bovigo::vfs::vfsStreamWrapper in conjunction with ext/zip.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 * @group       zip
 */
class vfsStreamZipTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * set up test environment
     */
    public function setUp()
    {
        if (extension_loaded('zip') === false) {
            $this->markTestSkipped('No ext/zip installed, skipping test.');
        }
        
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(vfsStream::newDirectory('root'));

    }

    /**
     * @test
     */
    public function createZipArchive()
    {
        $this->markTestSkipped('Zip extension can not work with vfsStream urls.');
        $zip = new ZipArchive();
        $this->assertTrue($zip->open(vfsStream::url('root/test.zip'), ZIPARCHIVE::CREATE));
        $this->assertTrue($zip->addFromString("testfile1.txt", "#1 This is a test string added as testfile1.txt.\n"));
        $this->assertTrue($zip->addFromString("testfile2.txt", "#2 This is a test string added as testfile2.txt.\n"));
        $zip->setArchiveComment('a test');
        var_dump($zip);
       # $this->assertTrue($zip->close());
        var_dump($zip->getStatusString());
        var_dump($zip->close());
        var_dump($zip->getStatusString());
        var_dump($zip);
        var_dump(file_exists(vfsStream::url('root/test.zip')));
    }
}
?>