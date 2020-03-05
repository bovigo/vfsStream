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
use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\doesNotContain;
use function stream_get_wrappers;
use function stream_wrapper_register;
use function stream_wrapper_unregister;

/**
 * Test for bovigo\vfs\StreamWrapper.
 */
class StreamWrapperUnregisterTestCase extends TestCase
{
    /**
     * @test
     */
    public function unregisterRegisteredUrlWrapper(): void
    {
        StreamWrapper::register();
        StreamWrapper::unregister();
        assertThat(stream_get_wrappers(), doesNotContain(vfsStream::SCHEME));
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function canNotUnregisterThirdPartyVfsScheme(): void
    {
        // Unregister possible registered URL wrapper.
        StreamWrapper::unregister();

        stream_wrapper_register(
            vfsStream::SCHEME,
            NewInstance::classname(StreamWrapper::class)
        );
        expect(static function (): void {
            StreamWrapper::unregister();
        })
          ->throws(vfsStreamException::class);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function canNotUnregisterWhenNotInRegisteredState(): void
    {
        StreamWrapper::register();
        stream_wrapper_unregister(vfsStream::SCHEME);
        expect(static function (): void {
            StreamWrapper::unregister();
        })
          ->throws(vfsStreamException::class);
    }

    /**
     * @test
     */
    public function unregisterWhenNotRegisteredDoesNotFail(): void
    {
        // Unregister possible registered URL wrapper.
        StreamWrapper::unregister();
        expect(static function (): void {
            StreamWrapper::unregister();
        })
          ->doesNotThrow();
    }
}
