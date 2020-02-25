<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs;

use bovigo\vfs\internal\Mode;
use bovigo\vfs\internal\OpenedFile;
use bovigo\vfs\internal\Path;
use bovigo\vfs\internal\Root;
use bovigo\vfs\internal\Type;
use const E_USER_WARNING;
use const LOCK_NB;
use const LOCK_UN;
use const STREAM_META_ACCESS;
use const STREAM_META_GROUP;
use const STREAM_META_GROUP_NAME;
use const STREAM_META_OWNER;
use const STREAM_META_OWNER_NAME;
use const STREAM_META_TOUCH;
use const STREAM_OPTION_BLOCKING;
use const STREAM_OPTION_READ_TIMEOUT;
use const STREAM_OPTION_WRITE_BUFFER;
use const STREAM_REPORT_ERRORS;
use const STREAM_URL_STAT_QUIET;
use function class_alias;
use function clearstatcache;
use function count;
use function explode;
use function in_array;
use function str_replace;
use function stream_get_wrappers;
use function stream_wrapper_register;
use function stream_wrapper_unregister;
use function strpos;
use function strstr;
use function substr;
use function time;
use function trigger_error;

/**
 * Stream wrapper to mock file system requests.
 */
class StreamWrapper
{
    /**
     * switch whether class has already been registered as stream wrapper or not
     *
     * @var  bool
     */
    protected static $registered = false;
    /**
     * root content
     *
     * @var  Root
     */
    protected static $root;
    /**
     * disk space quota
     *
     * @var  Quota
     */
    private static $quota;
    /**
     * shortcut to file container
     *
     * @var  OpenedFile|null
     */
    protected $file;
    /**
     * shortcut to directory container iterator
     *
     * @var  vfsDirectoryIterator|null
     */
    protected $dirIterator;

    /**
     * method to register the stream wrapper
     *
     * Please be aware that a call to this method will reset the root element
     * to null.
     * If the stream is already registered the method returns silently. If there
     * is already another stream wrapper registered for the scheme used by
     * vfsStream a vfsStreamException will be thrown.
     *
     * @throws  vfsStreamException
     */
    public static function register(): void
    {
        self::$root = Root::empty();
        self::$quota = Quota::unlimited();
        if (self::$registered === true) {
            return;
        }

        if (@stream_wrapper_register(vfsStream::SCHEME, self::class) === false) {
            throw new vfsStreamException(
                'A handler has already been registered for the ' . vfsStream::SCHEME . ' protocol.'
            );
        }

        self::$registered = true;
    }

    /**
     * Unregisters a previously registered URL wrapper for the vfs scheme.
     *
     * If this stream wrapper wasn't registered, the method returns silently.
     *
     * If unregistering fails, or if the URL wrapper for vfs:// was not
     * registered with this class, a vfsStreamException will be thrown.
     *
     * @throws vfsStreamException
     *
     * @since  1.6.0
     */
    public static function unregister(): void
    {
        if (! self::$registered) {
            if (in_array(vfsStream::SCHEME, stream_get_wrappers())) {
                throw new vfsStreamException(
                    'The URL wrapper for the protocol ' . vfsStream::SCHEME .
                    ' was not registered with this version of vfsStream.'
                );
            }

            return;
        }

        if (! @stream_wrapper_unregister(vfsStream::SCHEME)) {
            throw new vfsStreamException(
                'Failed to unregister the URL wrapper for the ' . vfsStream::SCHEME . ' protocol.'
            );
        }

        self::$registered = false;
    }

    /**
     * sets the root content
     */
    public static function setRoot(vfsDirectory $root): vfsDirectory
    {
        self::$root = new Root($root);
        clearstatcache();

        return $root;
    }

    /**
     * returns the root content
     */
    public static function getRoot(): ?vfsDirectory
    {
        if (self::$root->isEmpty()) {
            return null;
        }

        return self::$root->dir();
    }

    /**
     * sets quota for disk space
     *
     * @since  1.1.0
     */
    public static function setQuota(Quota $quota): void
    {
        self::$quota = $quota;
    }

    private function reportErrors(int $options): bool
    {
        return ($options & STREAM_REPORT_ERRORS) === STREAM_REPORT_ERRORS;
    }

    /**
     * open the stream
     *
     * @param string      $path        the path to open
     * @param string      $mode        mode for opening
     * @param int         $options     options for opening
     * @param string|null $opened_path full path that was actually opened
     */
    public function stream_open(string $path, string $mode, int $options, ?string $opened_path = null): bool
    {
        $extended = (strstr($mode, '+') !== false ? (true) : (false));
        $mode = str_replace(['t', 'b', '+'], '', $mode);
        if (in_array($mode, ['r', 'w', 'a', 'x', 'c']) === false) {
            if ($this->reportErrors($options)) {
                trigger_error(
                    'Illegal mode ' . $mode . ', use r, w, a, x  or c, flavoured with t, b and/or +',
                    E_USER_WARNING
                );
            }

            return false;
        }

        $internalMode = Mode::calculate($mode, $extended);
        $path = Path::resolve(vfsStream::path($path));
        $this->file = null;

        $file = self::$root->fileFor($path);
        if ($file !== null) {
            if ($mode === Mode::WRITE) {
                if ($this->reportErrors($options)) {
                    trigger_error(
                        'File ' . $path . ' already exists, can not open with mode x',
                        E_USER_WARNING
                    );
                }

                return false;
            }

            if (($mode === Mode::TRUNCATE || $mode === Mode::APPEND) &&
                $file->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup()) === false
            ) {
                return false;
            }

            if ($mode === Mode::TRUNCATE) {
                $this->file = $file->openWithTruncate($internalMode);
            } elseif ($mode === Mode::APPEND) {
                $this->file = $file->openForAppend($internalMode);
            } else {
                if (! $file->isReadable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup())) {
                    if ($this->reportErrors($options)) {
                        trigger_error('Permission denied', E_USER_WARNING);
                    }

                    return false;
                }
                $this->file = $file->open($internalMode);
            }

            return true;
        }

        if ($mode === Mode::READ) {
            if ($this->reportErrors($options)) {
                trigger_error(
                    'Can not open non-existing file ' . $path . ' for reading',
                    E_USER_WARNING
                );
            }

            return false;
        }

        $file = $this->createFile($path, $options);
        if ($file === false) {
            return false;
        }

        $this->file = $file->open($internalMode);

        return true;
    }

    /**
     * creates a file at given path
     *
     * @param string      $path    the path to open
     * @param int|null    $options options for opening
     *
     * @return  vfsFile|false
     */
    private function createFile(string $path, ?int $options = null)
    {
        $filepath = Path::split($path);
        if (!$filepath->hasDirname()) {
            if ($this->reportErrors($options)) {
                trigger_error('File ' . $filepath->basename() . ' does not exist', E_USER_WARNING);
            }

            return false;
        }

        $dir = self::$root->directoryFor($filepath->dirname());
        if ($dir === null) {
            if ($this->reportErrors($options)) {
                trigger_error('Directory ' . $filepath->dirname() . ' does not exist', E_USER_WARNING);
            }

            return false;
        }

        if ($dir->hasChild($filepath->basename()) === true) {
            if ($this->reportErrors($options)) {
                trigger_error(
                    'Directory ' . $filepath->dirname() . ' already contains a directory named ' .
                    $filepath->basename(),
                    E_USER_WARNING
                );
            }

            return false;
        }

        if ($dir->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup()) === false) {
            if ($this->reportErrors($options)) {
                trigger_error(
                    'Can not create new file in non-writable path ' . $filepath->dirname(),
                    E_USER_WARNING
                );
            }

            return false;
        }

        return vfsStream::newFile($filepath->basename())->at($dir);
    }

    /**
     * closes the stream
     *
     * @see     https://github.com/mikey179/vfsStream/issues/40
     */
    public function stream_close(): void
    {
        $this->file->lock($this, LOCK_UN);
    }

    /**
     * read the stream up to $count bytes
     *
     * @param int $count amount of bytes to read
     */
    public function stream_read(int $count): string
    {
        return $this->file->read($count);
    }

    /**
     * writes data into the stream
     *
     * @return  int     amount of bytes written
     */
    public function stream_write(string $data): int
    {
        if (self::$quota->isLimited()) {
            $data = substr($data, 0, self::$quota->spaceLeft(self::$root->usedSpace()));
        }

        return $this->file->write($data);
    }

    /**
     * truncates a file to a given length
     *
     * @param int $size length to truncate file to
     *
     * @since   1.1.0
     */
    public function stream_truncate(int $size): bool
    {
        if (self::$quota->isLimited() && $this->file->size() < $size) {
            $maxSize = self::$quota->spaceLeft(self::$root->usedSpace());
            if ($maxSize === 0) {
                return false;
            }

            if ($size > $maxSize) {
                $size = $maxSize;
            }
        }

        return $this->file->truncate($size);
    }

    /**
     * sets metadata like owner, user or permissions
     *
     * @param mixed $var
     *
     * @since   1.1.0
     */
    public function stream_metadata(string $path, int $option, $var): bool
    {
        $path = Path::resolve(vfsStream::path($path));

        /** @var BasicFile|null $content */
        $content = self::$root->itemFor($path);
        switch ($option) {
            case STREAM_META_TOUCH:
                if ($content === null) {
                    $content = $this->createFile($path, STREAM_REPORT_ERRORS);
                    // file creation may not be allowed at provided path
                    if ($content === false) {
                        return false;
                    }
                }

                $currentTime = time();
                $content->lastModified($var[0] ?? $currentTime);
                $content->lastAccessed($var[1] ?? $currentTime);

                return true;
            case STREAM_META_OWNER_NAME:
                return false;
            case STREAM_META_OWNER:
                if ($content === null) {
                    return false;
                }

                return $this->doPermChange(
                    $path,
                    $content,
                    static function (Inode $inode) use ($var): void {
                        $inode->chown($var);
                    }
                );
            case STREAM_META_GROUP_NAME:
                return false;
            case STREAM_META_GROUP:
                if ($content === null) {
                    return false;
                }

                return $this->doPermChange(
                    $path,
                    $content,
                    static function (Inode $inode) use ($var): void {
                        $inode->chgrp($var);
                    }
                );
            case STREAM_META_ACCESS:
                if ($content === null) {
                    return false;
                }

                return $this->doPermChange(
                    $path,
                    $content,
                    static function (Inode $inode) use ($var): void {
                        $inode->chmod($var);
                    }
                );
            default:
                return false;
        }
    }

    /**
     * executes given permission change when necessary rights allow such a change
     */
    private function doPermChange(string $path, Inode $inode, callable $change): bool
    {
        if (! $inode->isOwnedByUser(vfsStream::getCurrentUser())) {
            return false;
        }

        if (self::$root->dirName() !== $path) {
            $filepath = Path::split($path);
            $parent = self::$root->itemFor($filepath->dirname());
            if (! $parent->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup())) {
                return false;
            }
        }

        $change($inode);

        return true;
    }

    /**
     * checks whether stream is at end of file
     */
    public function stream_eof(): bool
    {
        return $this->file->eof();
    }

    /**
     * returns the current position of the stream
     */
    public function stream_tell(): int
    {
        return $this->file->bytesRead();
    }

    /**
     * seeks to the given offset
     */
    public function stream_seek(int $offset, int $whence): bool
    {
        return $this->file->seek($offset, $whence);
    }

    /**
     * flushes unstored data into storage
     */
    public function stream_flush(): bool
    {
        return true;
    }

    /**
     * returns status of stream
     *
     * @return int[]|false
     */
    public function stream_stat()
    {
        return $this->file->stat();
    }

    /**
     * retrieve the underlaying resource
     *
     * Please note that this method always returns false as there is no
     * underlaying resource to return.
     *
     * @see     https://github.com/mikey179/vfsStream/issues/3
     *
     * @since   0.9.0
     */
    public function stream_cast(int $cast_as): bool
    {
        return false;
    }

    /**
     * set lock status for stream
     *
     * @see     https://github.com/mikey179/vfsStream/issues/6
     * @see     https://github.com/mikey179/vfsStream/issues/31
     * @see     https://github.com/mikey179/vfsStream/issues/40
     *
     * @since   0.10.0
     */
    public function stream_lock(int $operation): bool
    {
        if ((LOCK_NB & $operation) === LOCK_NB) {
            $operation -= LOCK_NB;
        }

        return $this->file->lock($this, $operation);
    }

    /**
     * sets options on the stream
     *
     * @see     https://github.com/mikey179/vfsStream/issues/15
     * @see     http://www.php.net/manual/streamwrapper.stream-set-option.php
     */
    //phpcs:ignore SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
    public function stream_set_option(int $option, $arg1, $arg2): bool
    {
        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                // break omitted

            case STREAM_OPTION_READ_TIMEOUT:
                // break omitted

            case STREAM_OPTION_WRITE_BUFFER:
                // break omitted

            default:
                // nothing to do here
        }

        return false;
    }

    /**
     * remove the data under the given path
     */
    public function unlink(string $path): bool
    {
        $realPath = Path::resolve(vfsStream::path($path));
        $content = self::$root->itemFor($realPath);
        if ($content === null) {
            trigger_error('unlink(' . $path . '): No such file or directory', E_USER_WARNING);

            return false;
        }

        if ($content->type() !== TYPE::FILE) {
            trigger_error('unlink(' . $path . '): Operation not permitted', E_USER_WARNING);

            return false;
        }

        return $this->doUnlink($realPath);
    }

    /**
     * removes a path
     */
    protected function doUnlink(string $path): bool
    {
        if (self::$root->dirname() === $path) {
            // delete root? very brave. :)
            self::$root->unlink();
            clearstatcache();

            return true;
        }

        $filepath = Path::split($path);

        $dir = self::$root->directoryFor($filepath->dirname());
        if ($dir === null || ! $dir->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup())) {
            return false;
        }

        clearstatcache();

        return $dir->removeChild($filepath->basename());
    }

    /**
     * rename from one path to another
     *
     * @author  Benoit Aubuchon
     */
    public function rename(string $path_from, string $path_to): bool
    {
        $srcRealPath = Path::resolve(vfsStream::path($path_from));
        $dstRealPath = Path::resolve(vfsStream::path($path_to));
        $srcContent = self::$root->itemFor($srcRealPath);
        if ($srcContent === null) {
            trigger_error('No such file or directory', E_USER_WARNING);

            return false;
        }
        $dstNames = Path::split($dstRealPath);

        /** @var vfsDirectory|null $dstParentContent */
        $dstParentContent = self::$root->itemFor($dstNames->dirname());
        if ($dstParentContent === null) {
            trigger_error('No such file or directory', E_USER_WARNING);

            return false;
        }
        if (! $dstParentContent->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup())) {
            trigger_error('Permission denied', E_USER_WARNING);

            return false;
        }
        if ($dstParentContent->type() !== TYPE::DIR) {
            trigger_error('Target is not a directory', E_USER_WARNING);

            return false;
        }

        // remove old source first, so we can rename later
        // (renaming first would lead to not being able to remove the old path)
        if (! $this->doUnlink($srcRealPath)) {
            return false;
        }

        $dstContent = $srcContent;
        // Renaming the filename
        $dstContent->rename($dstNames->basename());
        // Copying to the destination
        $dstParentContent->addChild($dstContent);

        return true;
    }

    /**
     * creates a new directory
     */
    public function mkdir(string $path, int $mode, int $options): bool
    {
        $umask = vfsStream::umask();
        if (0 < $umask) {
            $permissions = $mode & ~$umask;
        } else {
            $permissions = $mode;
        }

        $path = Path::resolve(vfsStream::path($path));
        if (self::$root->itemFor($path) !== null) {
            trigger_error('mkdir(): Path vfs://' . $path . ' exists', E_USER_WARNING);
            return false;
        }

        if (self::$root->isEmpty()) {
            self::$root = new Root(vfsStream::newDirectory($path, $permissions));

            return true;
        }

        $maxDepth = count(explode('/', $path));
        $filepath = Path::split($path);
        $newDirs = $filepath->basename();
        $dir = null;
        $i = 0;
        while ($dir === null && $i < $maxDepth) {
            $dir = self::$root->itemFor($filepath->dirname());
            $filepath = Path::split($filepath->dirname());
            if ($dir === null) {
                $newDirs = $filepath->basename() . '/' . $newDirs;
            }

            $i++;
        }

        if ($dir === null
            || $dir->type() !== TYPE::DIR
            || $dir->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup()) === false) {
            return false;
        }

        $recursive = (STREAM_MKDIR_RECURSIVE & $options) !== 0 ? (true) : (false);
        if (strpos($newDirs, '/') !== false && $recursive === false) {
            return false;
        }

        vfsStream::newDirectory($newDirs, $permissions)->at($dir);

        return true;
    }

    /**
     * removes a directory
     *
     * @todo    consider $options with STREAM_MKDIR_RECURSIVE
     */
    public function rmdir(string $path, int $options): bool
    {
        $path = Path::resolve(vfsStream::path($path));

        $child = self::$root->directoryFor($path);
        if ($child === null) {
            return false;
        }

        // can only remove empty directories
        if (count($child->getChildren()) > 0) {
            return false;
        }

        if (self::$root->dirname() === $path) {
            // delete root? very brave. :)
            self::$root->unlink();
            clearstatcache();

            return true;
        }

        $filepath = Path::split($path);

        $dir = self::$root->directoryFor($filepath->dirname());
        if ($dir->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup()) === false) {
            return false;
        }

        clearstatcache();

        return $dir->removeChild($child->name());
    }

    /**
     * opens a directory
     */
    public function dir_opendir(string $path, int $options): bool
    {
        $path = Path::resolve(vfsStream::path($path));
        $dir = self::$root->directoryFor($path);
        if ($dir === null) {
            return false;
        }

        if (! $dir->isReadable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup())) {
            return false;
        }

        $this->dirIterator = $dir->getIterator();

        return true;
    }

    /**
     * reads directory contents
     *
     * @return  string|bool
     */
    public function dir_readdir()
    {
        $dir = $this->dirIterator->current();
        if ($dir === null) {
            return false;
        }

        $this->dirIterator->next();

        return $dir->name();
    }

    /**
     * reset directory iteration
     */
    public function dir_rewinddir(): bool
    {
        $this->dirIterator->rewind();

        return true;
    }

    /**
     * closes directory
     */
    public function dir_closedir(): bool
    {
        $this->dirIterator = null;

        return true;
    }

    /**
     * returns status of url
     *
     * @param string $path  path of url to return status for
     * @param int    $flags flags set by the stream API
     *
     * @return  mixed[]|bool
     */
    public function url_stat(string $path, int $flags)
    {
        $content = self::$root->itemFor(Path::resolve(vfsStream::path($path)));
        if ($content === null) {
            if (($flags & STREAM_URL_STAT_QUIET) !== STREAM_URL_STAT_QUIET) {
                trigger_error(' No such file or directory: ' . $path, E_USER_WARNING);
            }

            return false;
        }

        return $content->stat();
    }
}

class_alias('bovigo\vfs\StreamWrapper', 'org\bovigo\vfs\vfsStreamWrapper');
