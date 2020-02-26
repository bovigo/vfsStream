<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\internal;

use bovigo\vfs\BasicFile;
use bovigo\vfs\vfsDirectory;
use bovigo\vfs\vfsFile;
use function strlen;
use function substr;

/**
 * Helper for working with the root directory.
 *
 * @internal
 */
final class Root
{
    /** @var  vfsDirectory */
    private $dir;
    /** @var  bool */
    private $empty;

    public function __construct(vfsDirectory $dir)
    {
        $this->dir = $dir;
        $this->empty = false;
    }

    public static function empty(): self
    {
        // Using a directory with a name hopefully no-one else uses for their root path name.
        $r = new self(new vfsDirectory('.vfs'));
        $r->empty = true;

        return $r;
    }

    public function unlink(): void
    {
        $this->dir = new vfsDirectory('.vfs');
        $this->empty = true;
    }

    public function isEmpty(): bool
    {
        return $this->empty;
    }

    public function dir(): vfsDirectory
    {
        return $this->dir;
    }

    public function dirname(): string
    {
        return $this->dir->name();
    }

    public function usedSpace(): int
    {
        return $this->dir->sizeSummarized();
    }

    /**
     * returns content for given path
     */
    public function itemFor(string $path): ?BasicFile
    {
        if ($this->dir->name() === $path) {
            return $this->dir;
        }

        if ($this->isInRoot($path) && $this->dir->hasChild($path) === true) {
            return $this->dir->getChild($path);
        }

        return null;
    }

    /**
     * helper method to detect whether given path is in root path
     */
    private function isInRoot(string $path): bool
    {
        return substr($path, 0, strlen($this->dir->name())) === $this->dir->name();
    }

    public function directoryFor(string $path): ?vfsDirectory
    {
        $dir = $this->itemFor($path);
        if ($dir !== null && $dir instanceof vfsDirectory) {
            return $dir;
        }

        return null;
    }

    public function fileFor(string $path): ?vfsFile
    {
        $file = $this->itemFor($path);
        if ($file !== null && $file instanceof vfsFile) {
            return $file;
        }

        return null;
    }
}
