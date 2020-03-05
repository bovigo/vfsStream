<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs\tests;

use bovigo\callmap\NewInstance;
use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamException;
use bovigo\vfs\StreamWrapper;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\expect;
use function in_array;
use function stream_get_wrappers;
use function stream_wrapper_register;
use function stream_wrapper_unregister;

/**
 * Helper class for the test.
 */
class TestvfsStreamWrapper extends StreamWrapper
{
    /**
     * unregisters StreamWrapper
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
 * Test for bovigo\vfs\vfsStreamWrapper.
 */
class vfsStreamWrapperAlreadyRegisteredTestCase extends \BC_PHPUnit_Framework_TestCase
{
    /**
     * set up test environment
     */
    public function setUp()
    {
        TestvfsStreamWrapper::unregister();
        $mock = $this->bc_getMock('org\\bovigo\\vfs\\vfsStreamWrapper');
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
     * @expectedException  org\bovigo\vfs\vfsStreamException
     */
    public function registerOverAnotherStreamWrapper()
    {
        StreamWrapper::register();
    }
}
