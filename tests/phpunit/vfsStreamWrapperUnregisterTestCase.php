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
use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\doesNotContain;
use function stream_get_wrappers;
use function stream_wrapper_register;
use function stream_wrapper_unregister;

/**
 * Test for bovigo\vfs\StreamWrapper.
 */
class vfsStreamWrapperUnregisterTestCase extends \BC_PHPUnit_Framework_TestCase
{

    /**
     * Unregistering a registered URL wrapper.
     *
     * @test
     */
    public function unregisterRegisteredUrlWrapper()
    {
        StreamWrapper::register();
        StreamWrapper::unregister();
        $this->assertNotContains(vfsStream::SCHEME, stream_get_wrappers());
    }

    /**
     * Unregistering a third party wrapper for vfs:// fails.
     *
     * @test
     * @expectedException org\bovigo\vfs\vfsStreamException
     * @runInSeparateProcess
     */
    public function unregisterThirdPartyVfsScheme()
    {
        // Unregister possible registered URL wrapper.
        StreamWrapper::unregister();

        $mock = $this->bc_getMock('org\\bovigo\\vfs\\vfsStreamWrapper');
        stream_wrapper_register(vfsStream::SCHEME, get_class($mock));

        StreamWrapper::unregister();
    }

    /**
     * Unregistering when not in registered state will fail.
     *
     * @test
     * @expectedException org\bovigo\vfs\vfsStreamException
     * @runInSeparateProcess
     */
    public function unregisterWhenNotInRegisteredState()
    {
        StreamWrapper::register();
        stream_wrapper_unregister(vfsStream::SCHEME);
        StreamWrapper::unregister();
    }

    /**
     * Unregistering while not registers won't fail.
     *
     * @test
     */
    public function unregisterWhenNotRegistered()
    {
        // Unregister possible registered URL wrapper.
        StreamWrapper::unregister();

        $this->assertNotContains(vfsStream::SCHEME, stream_get_wrappers());
        StreamWrapper::unregister();
    }
}
