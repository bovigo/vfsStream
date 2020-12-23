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
use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamException;
use bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\doesNotContain;
use function stream_get_wrappers;
use function stream_wrapper_register;
use function stream_wrapper_unregister;

/**
 * Test for bovigo\vfs\vfsStreamWrapper.
 */
class vfsStreamWrapperUnregisterTestCase extends TestCase
{
    /**
     * @test
     */
    public function unregisterRegisteredUrlWrapper(): void
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::unregister();
        assertThat(stream_get_wrappers(), doesNotContain(vfsStream::SCHEME));
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function canNotUnregisterThirdPartyVfsScheme(): void
    {
        // Unregister possible registered URL wrapper.
        vfsStreamWrapper::unregister();

        stream_wrapper_register(
            vfsStream::SCHEME,
            NewInstance::classname(vfsStreamWrapper::class)
        );
        expect(static function (): void {
            vfsStreamWrapper::unregister();
        })
          ->throws(vfsStreamException::class);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function canNotUnregisterWhenNotInRegisteredState(): void
    {
        vfsStreamWrapper::register();
        stream_wrapper_unregister(vfsStream::SCHEME);
        expect(static function (): void {
            vfsStreamWrapper::unregister();
        })
          ->throws(vfsStreamException::class);
    }

    /**
     * @test
     */
    public function unregisterWhenNotRegisteredDoesNotFail(): void
    {
        // Unregister possible registered URL wrapper.
        vfsStreamWrapper::unregister();
        expect(static function (): void {
            vfsStreamWrapper::unregister();
        })
          ->doesNotThrow();
    }
}
