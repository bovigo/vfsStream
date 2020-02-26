<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  bovigo\vfs
 */

namespace bovigo\vfs\internal;

/**
 * @internal
 */
final class Type
{
    /**
     * stream content type: file
     */
    public const FILE = 0100000;
    /**
     * stream content type: directory
     */
    public const DIR = 0040000;
    /**
     * stream content type: symbolic link
     */
    // const LINK = 0120000;
    /**
     * stream content type: block
     */
    public const BLOCK = 0060000;
}
