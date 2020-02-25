<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\internal;

/**
 * Helper for mode handling.
 *
 * @internal
 */
final class Mode
{
    /**
     * open file for reading
     */
    public const READ = 'r';
    /**
     * truncate file
     */
    public const TRUNCATE = 'w';
    /**
     * set file pointer to end, append new data
     */
    public const APPEND = 'a';
    /**
     * set file pointer to start, overwrite existing data
     */
    public const WRITE = 'x';
    /**
     * set file pointer to start, overwrite existing data; or create file if
     * does not exist
     */
    public const WRITE_NEW = 'c';
    /**
     * file mode: read only
     */
    public const READONLY = 0;
    /**
     * file mode: write only
     */
    public const WRITEONLY = 1;
    /**
     * file mode: read and write
     */
    public const ALL = 2;

    /**
     * calculates the file mode
     *
     * @param string $mode     opening mode: r, w, a or x
     * @param bool   $extended true if + was set with opening mode
     */
    public static function calculate(string $mode, bool $extended): int
    {
        if ($extended === true) {
            return self::ALL;
        }

        if ($mode === self::READ) {
            return self::READONLY;
        }

        return self::WRITEONLY;
    }

}