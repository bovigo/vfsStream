<?php
declare(strict_types=1);
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
namespace org\bovigo\vfs;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\expect;
/**
 * Helper class for the test.
 *
 * Required to be able to reset the internal state of vfsStreamWrapper.
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
 * Test for org\bovigo\vfs\vfsStreamWrapper.
 */
class vfsStreamWrapperAlreadyRegisteredTestCase extends TestCase
{
    /**
     * clean up test environment
     */
    public function tearDown()
    {
        TestvfsStreamWrapper::unregister();
    }

    /**
     * @test
     */
    public function registerOverAnotherStreamWrapperThrowsException()
    {
        TestvfsStreamWrapper::unregister();
        stream_wrapper_register(
            vfsStream::SCHEME,
            NewInstance::classname(vfsStreamWrapper::class)
        );
        expect(function() { vfsStreamWrapper::register(); })
          ->throws(vfsStreamException::class);
    }
}
