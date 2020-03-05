<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\internal\Type;
use bovigo\vfs\vfsBlock;
use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamException;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function filetype;

/**
 * Test for bovigo\vfs\vfsBlock.
 */
class vfsBlockTestCase extends TestCase
{
    /**
     * @test
     */
    public function isOfTypeBlock(): void
    {
        assertThat((new vfsBlock('foo'))->type(), equals(Type::BLOCK));
    }

    /**
     * @test
     */
    public function appliesForSelf(): void
    {
        assertTrue((new vfsBlock('foo'))->appliesTo('foo'));
    }

    /**
     * @test
     */
    public function doesNotApplyForSubDirectories(): void
    {
        assertFalse((new vfsBlock('foo'))->appliesTo('foo/bar'));
    }

    /**
     * @test
     */
    public function doesNotApplyForOtherNames(): void
    {
        assertFalse((new vfsBlock('foo'))->appliesTo('bar'));
    }

    /**
     * @test
     */
    public function hasGivenName(): void
    {
        assertThat((new vfsBlock('foo'))->name(), equals('foo'));
    }

    /**
     * tests how external functions see this object
     *
     * @test
     */
    public function external(): void
    {
        $root = vfsStream::setup('root');
        $root->addChild(vfsStream::newBlock('foo'));
        assertThat(filetype(vfsStream::url('root/foo')), equals('block'));
    }

    /**
     * tests adding a complex structure
     *
     * @test
     */
    public function addStructure(): void
    {
        vfsStream::create([
            'topLevel' => [
                'thisIsAFile' => 'file contents',
                '[blockDevice]' => 'block contents',
            ],
        ]);
        assertThat(
            filetype(vfsStream::url('root/topLevel/blockDevice')),
            equals('block')
        );
    }

    /**
     * @test
     */
    public function createWithEmptyNameThrowsException(): void
    {
        expect(static function (): void {
            vfsStream::create([
                'topLevel' => [
                    'thisIsAFile' => 'file contents',
                    '[]' => 'block contents',
                ],
            ]);
        })->throws(vfsStreamException::class);
    }
}
