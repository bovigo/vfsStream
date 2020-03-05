<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\internal;

use function array_pop;
use function count;
use function explode;
use function implode;
use function strrpos;
use function substr;

/**
 * Helper methods for working with pathes.
 *
 * @internal
 */
final class Path
{
    /** @var  string */
    private $dirname;
    /** @var  string */
    private $basename;

    private function __construct(string $dirname, string $basename)
    {
        $this->dirname = $dirname;
        $this->basename = $basename;
    }

    public function dirname(): string
    {
        return $this->dirname;
    }

    public function hasDirname(): bool
    {
        return ! empty($this->dirname);
    }

    public function basename(): string
    {
        return $this->basename;
    }

    /**
     * splits path into its dirname and the basename
     */
    public static function split(string $path): self
    {
        $lastSlashPos = strrpos($path, '/');
        if ($lastSlashPos === false) {
            return new Path('', $path);
        }

        return new Path(
            substr($path, 0, $lastSlashPos),
            substr($path, $lastSlashPos + 1)
        );
    }

    /**
     * helper method to resolve a path from /foo/bar/. to /foo/bar
     */
    public static function resolve(string $path): string
    {
        $newPath = [];
        foreach (explode('/', $path) as $pathPart) {
            if ($pathPart === '.') {
                continue;
            }

            if ($pathPart !== '..') {
                $newPath[] = $pathPart;
            } elseif (count($newPath) > 1) {
                array_pop($newPath);
            }
        }

        return implode('/', $newPath);
    }
}
