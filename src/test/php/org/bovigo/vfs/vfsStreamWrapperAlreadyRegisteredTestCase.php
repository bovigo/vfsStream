<?php
/**
 * Test for org::bovigo::vfs::vfsStreamWrapper.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
require_once 'org/bovigo/vfs/vfsStream.php';
require_once 'PHPUnit/Framework/TestCase.php';
/**
 * Helper class for the test.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
class TestvfsStreamWrapper extends vfsStreamWrapper
{
    /**
     * unregisters vfsStreamWrapper
     */
    public static function unregister()
    {
        if (in_array(vfsStream::SCHEME, stream_get_wrappers()) === true) {
            stream_wrapper_unregister(vfsStream::SCHEME);
        }

        self::$registered = false;
    }
}
/**
 * Test for org::bovigo::vfs::vfsStreamWrapper.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
class vfsStreamWrapperAlreadyRegisteredTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * set up test environment
     */
    public function setUp()
    {
        TestvfsStreamWrapper::unregister();
        $mock = $this->getMock('vfsStreamWrapper');
        stream_wrapper_register(vfsStream::SCHEME, get_class($mock));
    }

    /**
     * clean up test environment
     */
    public function tearDown()
    {
        TestvfsStreamWrapper::unregister();
    }

    /**
     * registering the stream wrapper when another stream wrapper is already
     * registered for the vfs scheme should throw an exception
     *
     * @test
     * @expectedException  vfsStreamException
     */
    public function registerOverAnotherStreamWrapper()
    {
        vfsStreamWrapper::register();
    }
}
?>