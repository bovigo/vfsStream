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
/**
 * Stream wrapper to mock file system requests.
 */
class vfsStreamWrapper
{
    /**
     * open file for reading
     */
    const READ                   = 'r';
    /**
     * truncate file
     */
    const TRUNCATE               = 'w';
    /**
     * set file pointer to end, append new data
     */
    const APPEND                 = 'a';
    /**
     * set file pointer to start, overwrite existing data
     */
    const WRITE                  = 'x';
    /**
     * set file pointer to start, overwrite existing data; or create file if
     * does not exist
     */
    const WRITE_NEW              = 'c';
    /**
     * file mode: read only
     */
    const READONLY               = 0;
    /**
     * file mode: write only
     */
    const WRITEONLY              = 1;
    /**
     * file mode: read and write
     */
    const ALL                    = 2;
    /**
     * switch whether class has already been registered as stream wrapper or not
     *
     * @var  bool
     */
    protected static $registered = false;
    /**
     * root content
     *
     * @var  vfsStreamDirectory|null
     */
    protected static $root;
    /**
     * disk space quota
     *
     * @var  Quota
     */
    private static $quota;
    /**
     * file mode: read only, write only, all
     *
     * @var  int
     */
    protected $mode;
    /**
     * shortcut to file container
     *
     * @var  vfsStreamFile|null
     */
    protected $content;
    /**
     * shortcut to directory container
     *
     * @var  vfsStreamDirectory|null
     */
    protected $dir;
    /**
     * shortcut to directory container iterator
     *
     * @var  vfsStreamContainerIterator|null
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
    public static function register()
    {
        self::$root  = null;
        self::$quota = Quota::unlimited();
        if (true === self::$registered) {
            return;
        }

        if (@stream_wrapper_register(vfsStream::SCHEME, __CLASS__) === false) {
            throw new vfsStreamException('A handler has already been registered for the ' . vfsStream::SCHEME . ' protocol.');
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
     * @since  1.6.0
     */
    public static function unregister()
    {
        if (!self::$registered) {
            if (in_array(vfsStream::SCHEME, stream_get_wrappers())) {
                throw new vfsStreamException('The URL wrapper for the protocol ' . vfsStream::SCHEME . ' was not registered with this version of vfsStream.');
            }
            return;
        }

        if (!@stream_wrapper_unregister(vfsStream::SCHEME)) {
            throw new vfsStreamException('Failed to unregister the URL wrapper for the ' . vfsStream::SCHEME . ' protocol.');
        }

        self::$registered = false;
    }

    /**
     * sets the root content
     *
     * @param   vfsStreamDirectory  $root
     * @return  vfsStreamDirectory
     */
    public static function setRoot(vfsStreamDirectory $root): vfsStreamDirectory
    {
        self::$root = $root;
        clearstatcache();
        return self::$root;
    }

    /**
     * returns the root content
     *
     * @return  vfsStreamDirectory|null
     */
    public static function getRoot(): ?vfsStreamDirectory
    {
        return self::$root;
    }

    /**
     * sets quota for disk space
     *
     * @param  Quota  $quota
     * @since  1.1.0
     */
    public static function setQuota(Quota $quota)
    {
        self::$quota = $quota;
    }

    /**
     * returns content for given path
     *
     * @param   string  $path
     * @return  vfsStreamContent|null
     */
    protected function getContent(string $path): ?vfsStreamContent
    {
        if (null === self::$root) {
            return null;
        }

        if (self::$root->getName() === $path) {
            return self::$root;
        }

        if ($this->isInRoot($path) && self::$root->hasChild($path) === true) {
            return self::$root->getChild($path);
        }

        return null;
    }

    /**
     * helper method to detect whether given path is in root path
     *
     * @param   string  $path
     * @return  bool
     */
    private function isInRoot(string $path): bool
    {
        return substr($path, 0, strlen(self::$root->getName())) === self::$root->getName();
    }

    /**
     * returns content for given path but only when it is of given type
     *
     * @param   string  $path
     * @param   int     $type
     * @return  vfsStreamContent|null
     */
    protected function getContentOfType(string $path, int $type): ?vfsStreamContent
    {
        $content = $this->getContent($path);
        if (null !== $content && $content->getType() === $type) {
            return $content;
        }

        return null;
    }

    /**
     * splits path into its dirname and the basename
     *
     * @param   string  $path
     * @return  string[]
     */
    protected function splitPath(string $path): array
    {
        $lastSlashPos = strrpos($path, '/');
        if (false === $lastSlashPos) {
            return ['dirname' => '', 'basename' => $path];
        }

        return [
            'dirname'  => substr($path, 0, $lastSlashPos),
            'basename' => substr($path, $lastSlashPos + 1)
        ];
    }

    /**
     * helper method to resolve a path from /foo/bar/. to /foo/bar
     *
     * @param   string  $path
     * @return  string
     */
    protected function resolvePath(string $path): string
    {
        $newPath  = [];
        foreach (explode('/', $path) as $pathPart) {
            if ('.' !== $pathPart) {
                if ('..' !== $pathPart) {
                    $newPath[] = $pathPart;
                } elseif (count($newPath) > 1) {
                    array_pop($newPath);
                }
            }
        }

        return implode('/', $newPath);
    }

    /**
     * open the stream
     *
     * @param   string       $path         the path to open
     * @param   string       $mode         mode for opening
     * @param   int          $options      options for opening
     * @param   string|null  $opened_path  full path that was actually opened
     * @return  bool
     */
    public function stream_open(string $path, string $mode, int $options, string $opened_path = null): bool
    {
        $extended = ((strstr($mode, '+') !== false) ? (true) : (false));
        $mode     = str_replace(['t', 'b', '+'], '', $mode);
        if (in_array($mode, ['r', 'w', 'a', 'x', 'c']) === false) {
            if (($options & STREAM_REPORT_ERRORS) === STREAM_REPORT_ERRORS) {
                trigger_error('Illegal mode ' . $mode . ', use r, w, a, x  or c, flavoured with t, b and/or +', E_USER_WARNING);
            }

            return false;
        }

        $this->mode    = $this->calculateMode($mode, $extended);
        $path          = $this->resolvePath(vfsStream::path($path));
        $this->content = null;

        /** @var vfsStreamFile|null $content */
        $content = $this->getContentOfType($path, vfsStreamContent::TYPE_FILE);
        if (null !== $content) {
            $this->content = $content;
            if (self::WRITE === $mode) {
                if (($options & STREAM_REPORT_ERRORS) === STREAM_REPORT_ERRORS) {
                    trigger_error('File ' . $path . ' already exists, can not open with mode x', E_USER_WARNING);
                }

                return false;
            }

            if (
                (self::TRUNCATE === $mode || self::APPEND === $mode) &&
                $this->content->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup()) === false
            ) {
                return false;
            }

            if (self::TRUNCATE === $mode) {
                $this->content->openWithTruncate();
            } elseif (self::APPEND === $mode) {
                $this->content->openForAppend();
            } else {
                if (!$this->content->isReadable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup())) {
                    if (($options & STREAM_REPORT_ERRORS) === STREAM_REPORT_ERRORS) {
                        trigger_error('Permission denied', E_USER_WARNING);
                    }
                    return false;
                }
                $this->content->open();
            }

            return true;
        }

        $content = $this->createFile($path, $mode, $options);
        if (false === $content) {
            return false;
        }

        $this->content = $content;
        return true;
    }

    /**
     * creates a file at given path
     *
     * @param   string       $path     the path to open
     * @param   string|null  $mode     mode for opening
     * @param   int|null     $options  options for opening
     * @return  vfsStreamFile|false
     */
    private function createFile(string $path, string $mode = null, int $options = null)
    {
        $names = $this->splitPath($path);
        if (empty($names['dirname']) === true) {
            if (($options & STREAM_REPORT_ERRORS) === STREAM_REPORT_ERRORS) {
                trigger_error('File ' . $names['basename'] . ' does not exist', E_USER_WARNING);
            }

            return false;
        }

        /** @var vfsStreamDirectory|null $dir */
        $dir = $this->getContentOfType($names['dirname'], vfsStreamContent::TYPE_DIR);
        if (null === $dir) {
            if (($options & STREAM_REPORT_ERRORS) === STREAM_REPORT_ERRORS) {
                trigger_error('Directory ' . $names['dirname'] . ' does not exist', E_USER_WARNING);
            }

            return false;
        } elseif ($dir->hasChild($names['basename']) === true) {
            if (($options & STREAM_REPORT_ERRORS) === STREAM_REPORT_ERRORS) {
                trigger_error('Directory ' . $names['dirname'] . ' already contains a director named ' . $names['basename'], E_USER_WARNING);
            }

            return false;
        }

        if (self::READ === $mode) {
            if (($options & STREAM_REPORT_ERRORS) === STREAM_REPORT_ERRORS) {
                trigger_error('Can not open non-existing file ' . $path . ' for reading', E_USER_WARNING);
            }

            return false;
        }

        if ($dir->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup()) === false) {
            if (($options & STREAM_REPORT_ERRORS) === STREAM_REPORT_ERRORS) {
                trigger_error('Can not create new file in non-writable path ' . $names['dirname'], E_USER_WARNING);
            }

            return false;
        }

        /** @var vfsStreamFile $file */
        $file = vfsStream::newFile($names['basename'])->at($dir);

        return $file;
    }

    /**
     * calculates the file mode
     *
     * @param   string  $mode      opening mode: r, w, a or x
     * @param   bool    $extended  true if + was set with opening mode
     * @return  int
     */
    protected function calculateMode(string $mode, bool $extended): int
    {
        if (true === $extended) {
            return self::ALL;
        }

        if (self::READ === $mode) {
            return self::READONLY;
        }

        return self::WRITEONLY;
    }

    /**
     * closes the stream
     *
     * @see     https://github.com/mikey179/vfsStream/issues/40
     */
    public function stream_close()
    {
        $this->content->lock($this, LOCK_UN);
    }

    /**
     * read the stream up to $count bytes
     *
     * @param   int     $count  amount of bytes to read
     * @return  string
     */
    public function stream_read(int $count): string
    {
        if (self::WRITEONLY === $this->mode) {
            return '';
        }

        if ($this->content->isReadable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup()) === false) {
            return '';
        }

        return $this->content->read($count);
    }

    /**
     * writes data into the stream
     *
     * @param   string  $data
     * @return  int     amount of bytes written
     */
    public function stream_write(string $data): int
    {
        if (self::READONLY === $this->mode) {
            return 0;
        }

        if ($this->content->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup()) === false) {
            return 0;
        }

        if (self::$quota->isLimited()) {
            $data = substr($data, 0, self::$quota->spaceLeft(self::$root->sizeSummarized()));
        }

        return $this->content->write($data);
    }

    /**
     * truncates a file to a given length
     *
     * @param   int  $size  length to truncate file to
     * @return  bool
     * @since   1.1.0
     */
    public function stream_truncate(int $size): bool
    {
        if (self::READONLY === $this->mode) {
            return false;
        }

        if ($this->content->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup()) === false) {
            return false;
        }

        if ($this->content->getType() !== vfsStreamContent::TYPE_FILE) {
            return false;
        }

        if (self::$quota->isLimited() && $this->content->size() < $size) {
            $maxSize = self::$quota->spaceLeft(self::$root->sizeSummarized());
            if (0 === $maxSize) {
                return false;
            }

            if ($size > $maxSize) {
                $size = $maxSize;
            }
        }

        return $this->content->truncate($size);
    }

    /**
     * sets metadata like owner, user or permissions
     *
     * @param   string  $path
     * @param   int     $option
     * @param   mixed   $var
     * @return  bool
     * @since   1.1.0
     */
    public function stream_metadata(string $path, int $option, $var): bool
    {
        $path = $this->resolvePath(vfsStream::path($path));

        /** @var vfsStreamAbstractContent|null $content */
        $content = $this->getContent($path);
        switch ($option) {
            case STREAM_META_TOUCH:
                if (null === $content) {
                    $content = $this->createFile($path, null, STREAM_REPORT_ERRORS);
                    // file creation may not be allowed at provided path
                    if (false === $content) {
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
                if (null === $content) {
                    return false;
                }

                return $this->doPermChange(
                    $path,
                    $content,
                    function() use ($content, $var) { $content->chown($var); }
                );

            case STREAM_META_GROUP_NAME:
                return false;

            case STREAM_META_GROUP:
                if (null === $content) {
                    return false;
                }

                return $this->doPermChange(
                    $path,
                    $content,
                    function() use ($content, $var) { $content->chgrp($var); }
                );

            case STREAM_META_ACCESS:
                if (null === $content) {
                    return false;
                }

                return $this->doPermChange(
                    $path,
                    $content,
                    function() use ($content, $var) { $content->chmod($var); }
                );

            default:
                return false;
        }
    }

    /**
     * executes given permission change when necessary rights allow such a change
     *
     * @param   string                    $path
     * @param   vfsStreamAbstractContent  $content
     * @param   callable                  $change
     * @return  bool
     */
    private function doPermChange(string $path, vfsStreamAbstractContent $content, callable $change): bool
    {
        if (!$content->isOwnedByUser(vfsStream::getCurrentUser())) {
            return false;
        }

        if (self::$root->getName() !== $path) {
            $names   = $this->splitPath($path);
            $parent = $this->getContent($names['dirname']);
            if (!$parent->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup())) {
                return false;
            }
        }

        $change();
        return true;
    }

    /**
     * checks whether stream is at end of file
     *
     * @return  bool
     */
    public function stream_eof(): bool
    {
        return $this->content->eof();
    }

    /**
     * returns the current position of the stream
     *
     * @return  int
     */
    public function stream_tell(): int
    {
        return $this->content->getBytesRead();
    }

    /**
     * seeks to the given offset
     *
     * @param   int   $offset
     * @param   int   $whence
     * @return  bool
     */
    public function stream_seek(int $offset, int $whence): bool
    {
        return $this->content->seek($offset, $whence);
    }

    /**
     * flushes unstored data into storage
     *
     * @return  bool
     */
    public function stream_flush(): bool
    {
        return true;
    }

    /**
     * returns status of stream
     *
     * @return  array
     */
    public function stream_stat(): array
    {
        $fileStat = [
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => $this->content->getType() | $this->content->getPermissions(),
            'nlink'   => 0,
            'uid'     => $this->content->getUser(),
            'gid'     => $this->content->getGroup(),
            'rdev'    => 0,
            'size'    => $this->content->size(),
            'atime'   => $this->content->fileatime(),
            'mtime'   => $this->content->filemtime(),
            'ctime'   => $this->content->filectime(),
            'blksize' => -1,
            'blocks'  => -1
        ];
        return array_merge(array_values($fileStat), $fileStat);
    }

    /**
     * retrieve the underlaying resource
     *
     * Please note that this method always returns false as there is no
     * underlaying resource to return.
     *
     * @param   int  $cast_as
     * @since   0.9.0
     * @see     https://github.com/mikey179/vfsStream/issues/3
     * @return  bool
     */
    public function stream_cast(int $cast_as): bool
    {
        return false;
    }

    /**
     * set lock status for stream
     *
     * @param   int   $operation
     * @return  bool
     * @since   0.10.0
     * @see     https://github.com/mikey179/vfsStream/issues/6
     * @see     https://github.com/mikey179/vfsStream/issues/31
     * @see     https://github.com/mikey179/vfsStream/issues/40
     */
    public function stream_lock(int $operation): bool
    {
        if ((LOCK_NB & $operation) == LOCK_NB) {
            $operation = $operation - LOCK_NB;
        }

        return $this->content->lock($this, $operation);
    }

    /**
     * sets options on the stream
     *
     * @param   int   $option  key of option to set
     * @param   int   $arg1
     * @param   int   $arg2
     * @return  bool
     * @since   0.10.0
     * @see     https://github.com/mikey179/vfsStream/issues/15
     * @see     http://www.php.net/manual/streamwrapper.stream-set-option.php
     */
    public function stream_set_option(int $option, $arg1, $arg2)
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
     *
     * @param   string  $path
     * @return  bool
     */
    public function unlink(string $path): bool
    {
        $realPath = $this->resolvePath(vfsStream::path($path));
        $content  = $this->getContent($realPath);
        if (null === $content) {
            trigger_error('unlink(' . $path . '): No such file or directory', E_USER_WARNING);
            return false;
        }

        if ($content->getType() !== vfsStreamContent::TYPE_FILE) {
            trigger_error('unlink(' . $path . '): Operation not permitted', E_USER_WARNING);
            return false;
        }

        return $this->doUnlink($realPath);
    }

    /**
     * removes a path
     *
     * @param   string  $path
     * @return  bool
     */
    protected function doUnlink(string $path): bool
    {
        if (self::$root->getName() === $path) {
            // delete root? very brave. :)
            self::$root = null;
            clearstatcache();
            return true;
        }

        $names = $this->splitPath($path);

        /** @var vfsStreamDirectory $content */
        $content = $this->getContent($names['dirname']);
        if (!$content->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup())) {
            return false;
        }

        clearstatcache();
        return $content->removeChild($names['basename']);
    }

    /**
     * rename from one path to another
     *
     * @param   string  $path_from
     * @param   string  $path_to
     * @return  bool
     * @author  Benoit Aubuchon
     */
    public function rename(string $path_from, string $path_to): bool
    {
        $srcRealPath = $this->resolvePath(vfsStream::path($path_from));
        $dstRealPath = $this->resolvePath(vfsStream::path($path_to));
        $srcContent  = $this->getContent($srcRealPath);
        if (null === $srcContent) {
            trigger_error('No such file or directory', E_USER_WARNING);
            return false;
        }
        $dstNames = $this->splitPath($dstRealPath);

        /** @var vfsStreamDirectory|null $dstParentContent */
        $dstParentContent = $this->getContent($dstNames['dirname']);
        if (null === $dstParentContent) {
            trigger_error('No such file or directory', E_USER_WARNING);
            return false;
        }
        if (!$dstParentContent->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup())) {
            trigger_error('Permission denied', E_USER_WARNING);
            return false;
        }
        if ($dstParentContent->getType() !== vfsStreamContent::TYPE_DIR) {
            trigger_error('Target is not a directory', E_USER_WARNING);
            return false;
        }

        // remove old source first, so we can rename later
        // (renaming first would lead to not being able to remove the old path)
        if (!$this->doUnlink($srcRealPath)) {
            return false;
        }

        $dstContent = $srcContent;
        // Renaming the filename
        $dstContent->rename($dstNames['basename']);
        // Copying to the destination
        $dstParentContent->addChild($dstContent);
        return true;
    }

    /**
     * creates a new directory
     *
     * @param   string  $path
     * @param   int     $mode
     * @param   int     $options
     * @return  bool
     */
    public function mkdir(string $path, int $mode, int $options): bool
    {
        $umask = vfsStream::umask();
        if (0 < $umask) {
            $permissions = $mode & ~$umask;
        } else {
            $permissions = $mode;
        }

        $path = $this->resolvePath(vfsStream::path($path));
        if (null !== $this->getContent($path)) {
            trigger_error('mkdir(): Path vfs://' . $path . ' exists', E_USER_WARNING);
            return false;
        }

        if (null === self::$root) {
            self::$root = vfsStream::newDirectory($path, $permissions);
            return true;
        }

        $maxDepth = count(explode('/', $path));
        $names    = $this->splitPath($path);
        $newDirs  = $names['basename'];
        $dir      = null;
        $i        = 0;
        while ($dir === null && $i < $maxDepth) {
            $dir     = $this->getContent($names['dirname']);
            $names   = $this->splitPath($names['dirname']);
            if (null == $dir) {
                $newDirs = $names['basename'] . '/' . $newDirs;
            }

            $i++;
        }

        if (null === $dir
          || $dir->getType() !== vfsStreamContent::TYPE_DIR
          || $dir->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup()) === false) {
            return false;
        }

        $recursive = ((STREAM_MKDIR_RECURSIVE & $options) !== 0) ? (true) : (false);
        if (strpos($newDirs, '/') !== false && false === $recursive) {
            return false;
        }

        vfsStream::newDirectory($newDirs, $permissions)->at($dir);
        return true;
    }

    /**
     * removes a directory
     *
     * @param   string  $path
     * @param   int     $options
     * @return  bool
     * @todo    consider $options with STREAM_MKDIR_RECURSIVE
     */
    public function rmdir(string $path, int $options): bool
    {
        $path = $this->resolvePath(vfsStream::path($path));

        /** @var vfsStreamDirectory|null $child */
        $child = $this->getContentOfType($path, vfsStreamContent::TYPE_DIR);
        if (null === $child) {
            return false;
        }

        // can only remove empty directories
        if (count($child->getChildren()) > 0) {
            return false;
        }

        if (self::$root->getName() === $path) {
            // delete root? very brave. :)
            self::$root = null;
            clearstatcache();
            return true;
        }

        $names = $this->splitPath($path);

        /** @var vfsStreamDirectory $dir */
        $dir = $this->getContentOfType($names['dirname'], vfsStreamContent::TYPE_DIR);
        if ($dir->isWritable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup()) === false) {
            return false;
        }

        clearstatcache();
        return $dir->removeChild($child->getName());
    }

    /**
     * opens a directory
     *
     * @param   string  $path
     * @param   int     $options
     * @return  bool
     */
    public function dir_opendir(string $path, int $options): bool
    {
        $path      = $this->resolvePath(vfsStream::path($path));
        $this->dir = null;

        /** @var vfsStreamDirectory|null $dir */
        $dir = $this->getContentOfType($path, vfsStreamContent::TYPE_DIR);
        if (null === $dir) {
            return false;
        }

        $this->dir = $dir;
        if (!$this->dir->isReadable(vfsStream::getCurrentUser(), vfsStream::getCurrentGroup())) {
            return false;
        }

        $this->dirIterator = $this->dir->getIterator();
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
        if (null === $dir) {
            return false;
        }

        $this->dirIterator->next();
        return $dir->getName();
    }

    /**
     * reset directory iteration
     *
     * @return  bool
     */
    public function dir_rewinddir()
    {
        return $this->dirIterator->rewind();
    }

    /**
     * closes directory
     *
     * @return  bool
     */
    public function dir_closedir(): bool
    {
        $this->dirIterator = null;
        return true;
    }

    /**
     * returns status of url
     *
     * @param   string  $path   path of url to return status for
     * @param   int     $flags  flags set by the stream API
     * @return  array|bool
     */
    public function url_stat(string $path, int $flags)
    {
        /** @var vfsStreamAbstractContent|null $content */
        $content = $this->getContent($this->resolvePath(vfsStream::path($path)));
        if (null === $content) {
            if (($flags & STREAM_URL_STAT_QUIET) != STREAM_URL_STAT_QUIET) {
                trigger_error(' No such file or directory: ' . $path, E_USER_WARNING);
            }

            return false;

        }

        $fileStat = [
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => $content->getType() | $content->getPermissions(),
            'nlink'   => 0,
            'uid'     => $content->getUser(),
            'gid'     => $content->getGroup(),
            'rdev'    => 0,
            'size'    => $content->size(),
            'atime'   => $content->fileatime(),
            'mtime'   => $content->filemtime(),
            'ctime'   => $content->filectime(),
            'blksize' => -1,
            'blocks'  => -1
        ];
        return array_merge(array_values($fileStat), $fileStat);
    }
}
