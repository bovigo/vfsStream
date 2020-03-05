<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests;

use bovigo\callmap\NewInstance;
use bovigo\vfs\StreamWrapper;
use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamException;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\expect;
use function in_array;
use function stream_get_wrappers;
use function stream_wrapper_register;
use function stream_wrapper_unregister;

/**
 * Helper class for the test.
 *
 * Required to be able to reset the internal state of vfsStreamWrapper.
 */
class TestStreamWrapper extends StreamWrapper
{
    /**
     * unregisters StreamWrapper
     */
    public static function unregister(): void
    {
        if (in_array(vfsStream::SCHEME, stream_get_wrappers()) === true) {
            stream_wrapper_unregister(vfsStream::SCHEME);
        }

        self::$registered = false;
    }
}

/**
 * Test for bovigo\vfs\StreamWrapper.
 */
class StreamWrapperAlreadyRegisteredTestCase extends TestCase
{
    /**
     * clean up test environment
     */
    protected function tearDown(): void
    {
        TestStreamWrapper::unregister();
    }

    /**
     * @test
     */
    public function registerOverAnotherStreamWrapperThrowsException(): void
    {
        TestStreamWrapper::unregister();
        stream_wrapper_register(
            vfsStream::SCHEME,
            NewInstance::classname(StreamWrapper::class)
        );
        expect(static function (): void {
            StreamWrapper::register();
        })
          ->throws(vfsStreamException::class);
    }
}
