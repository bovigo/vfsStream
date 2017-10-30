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

use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\doesNotContain;
/**
 * Test for org\bovigo\vfs\vfsStreamWrapper.
 */
class vfsStreamWrapperUnregisterTestCase extends TestCase
{

    /**
     * @test
     */
    public function unregisterRegisteredUrlWrapper()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::unregister();
        assertThat(stream_get_wrappers(), doesNotContain(vfsStream::SCHEME));
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function canNotUnregisterThirdPartyVfsScheme()
    {
        // Unregister possible registered URL wrapper.
        vfsStreamWrapper::unregister();

        stream_wrapper_register(
          vfsStream::SCHEME,
          NewInstance::classname(vfsStreamWrapper::class)
        );
        expect(function() { vfsStreamWrapper::unregister(); })
          ->throws(vfsStreamException::class);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function canNotUnregisterWhenNotInRegisteredState()
    {
        vfsStreamWrapper::register();
        stream_wrapper_unregister(vfsStream::SCHEME);
        expect(function() { vfsStreamWrapper::unregister(); })
          ->throws(vfsStreamException::class);
    }

    /**
     * @test
     */
    public function unregisterWhenNotRegisteredDoesNotFail()
    {
        // Unregister possible registered URL wrapper.
        vfsStreamWrapper::unregister();
        expect(function() { vfsStreamWrapper::unregister(); })
          ->doesNotThrow();
    }
}
