<?php
/**
 * Stream wrapper to mock file system requests.
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo_vfs
 */
/**
 * @ignore
 */
require_once dirname(__FILE__) . '/vfsStreamDirectory.php';
require_once dirname(__FILE__) . '/vfsStreamFile.php';
require_once dirname(__FILE__) . '/vfsStreamException.php';
/**
 * Stream wrapper to mock file system requests.
 *
 * @package     bovigo_vfs
 */
class vfsStreamWrapper
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
     * @var  vfsStreamContent
     */
    protected static $root;
    /**
     * shortcut to file container
     *
     * @var  vfsStreamFile
     */
    protected $content;
    /**
     * shortcut to directory container
     *
     * @var  vfsStreamDirectory
     */
    protected $dir;

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
        self::$root = null;
        if (true === self::$registered) {
            return;
        }

        if (@stream_wrapper_register(vfsStream::SCHEME, __CLASS__) === false) {
            throw new vfsStreamException('A handler has already been registered for the ' . vfsStream::SCHEME . ' protocol.');
        }

        self::$registered = true;
    }

    /**
     * sets the root content
     *
     * @param  vfsStreamContent  $root
     */
    public static function setRoot(vfsStreamContent $root)
    {
        self::$root = $root;
    }

    /**
     * returns the root content
     *
     * @return  vfsStreamContent
     */
    public static function getRoot()
    {
        return self::$root;
    }

    /**
     * returns content for given path
     *
     * @param   string            $path
     * @return  vfsStreamContent
     */
    protected function getContent($path)
    {
        if (null === self::$root) {
            return null;
        }
        
        if (self::$root->getName() === $path) {
            return self::$root;
        }
        
        if (self::$root->hasChild($path) === true) {
            return self::$root->getChild($path);
        }
        
        return null;
    }

    /**
     * returns content for given path but only when it is of given type
     *
     * @param   string            $path
     * @param   int               $type
     * @return  vfsStreamContent
     */
    protected function getContentOfType($path, $type)
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
     * @return  array
     */
    protected function splitPath($path)
    {
        $lastSlashPos = strrpos($path, '/');
        if (false === $lastSlashPos) {
            return array('dirname' => '', 'basename' => $path);
        }
        
        return array('dirname'  => substr($path, 0, $lastSlashPos),
                     'basename' => substr($path, $lastSlashPos + 1)
               );
    }

    /**
     * open the stream
     *
     * @param   string  $path         the path to open
     * @param   string  $mode         mode for opening
     * @param   string  $options      options for opening
     * @param   string  $opened_path  full path that was actually opened
     * @return  bool
     * @todo    evaluate $mode and take action regarding to its value
     */
    public function stream_open($path, $mode, $options, $opened_path)
    {
        $path          = vfsStream::path($path);
        $this->content = $this->getContentOfType($path, vfsStreamContent::TYPE_FILE);
        if (null !== $this->content) {
            $this->content->seek(0, SEEK_SET);
            return true;
        }
        
        $names = $this->splitPath($path);
        $dir   = $this->getContentOfType($names['dirname'], vfsStreamContent::TYPE_DIR);
        // parent directory does not exist, or it does exist but then already
        // a directory with the basename exists
        if (null === $dir  || $dir->hasChild($names['basename']) === true) {
            return false;
        }
        
        $this->content = vfsStream::newFile($names['basename'])->at($dir);
        return true;
    }

    /**
     * closes the stream
     */
    public function stream_close()
    {
        // nothing to do
    }

    /**
     * read the stream up to $count bytes
     *
     * @param   int     $count  amount of bytes to read
     * @return  string
     */
    public function stream_read($count)
    {
        return $this->content->read($count);
    }

    /**
     * writes data into the stream
     *
     * @param   string  $data
     * @return  int     amount of bytes written
     */
    public function stream_write($data)
    {
        return $this->content->write($data);
    }

    /**
     * checks whether stream is at end of file
     *
     * @return  bool
     */
    public function stream_eof()
    {
        return $this->content->eof();
    }

    /**
     * returns the current position of the stream
     *
     * @return  int
     */
    public function stream_tell()
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
    public function stream_seek($offset, $whence)
    {
        return $this->content->seek($offset, $whence);
    }

    /**
     * flushes unstored data into storage
     *
     * @return  bool
     */
    public function stream_flush()
    {
        return true;
    }

    /**
     * returns status of stream
     *
     * @return  array
     * @todo    implement correct group and user id handling based on content
     * @todo    implement correct file mode handling based on content
     */
    public function stream_stat()
    {
        return array(2       => $this->content->getType() + octdec(0777),
                     4       => 0,
                     5       => 0,
                     7       => $this->content->size(),
                     9       => $this->content->filemtime(),
                     'mode'  => $this->content->getType() + octdec(0777),
                     'uid'   => 0,
                     'gid'   => 0,
                     'size'  => $this->content->size(),
                     'mtime' => $this->content->filemtime()
               );
    }

    /**
     * remove the data under the given path
     *
     * @param   string  $path
     * @return  bool
     */
    public function unlink($path)
    {
        $realPath = vfsStream::path($path);
        $content  = $this->getContent($realPath);
        if (null === $content) {
            return false;
        }
        
        if (self::$root->getName() === $realPath) {
            // delete root? very brave. :)
            self::$root = null;
            clearstatcache();
            return true;
        }
        
        $names   = $this->splitPath($realPath);
        $content = $this->getContent($names['dirname']);
        clearstatcache();
        return $content->removeChild($names['basename']);
    }

    /**
     * rename from one path to another
     *
     * @param   string  $path_from
     * @param   string  $path_to
     * @return  bool
     * @todo    implement :)
     */
    public function rename($path_from, $path_to)
    {
        return false;
    }

    /**
     * creates a new directory
     *
     * @param   string  $path
     * @param   int     $mode
     * @param   int     $options
     * @return  bool
     * @todo    set $mode on new directory
     */
    public function mkdir($path, $mode, $options)
    {
        $path = vfsStream::path($path);
        if (null === self::$root) {
            self::$root = vfsStream::newDirectory($path);
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
            $newDirs = $names['basename'] . '/' . $newDirs;
            $i++;
        }
        
        if (null === $dir || $dir->getType() !== vfsStreamContent::TYPE_DIR) {
            return false;
        }
        
        $newDirs = str_replace($dir->getName() . '/', '', $newDirs);
        $recursive = ((STREAM_MKDIR_RECURSIVE & $options) !== 0) ? (true) : (false);
        if (strpos($newDirs, '/') !== false && false === $recursive) {
            return false;
        }

        vfsStream::newDirectory($newDirs)->at($dir);
        return true;
    }

    /**
     * removes a directory
     *
     * @param   string  $path
     * @param   int     $options
     * @return  bool
     * @todo    implement :)
     */
    public function rmdir($path, $options)
    {
        $path  = vfsStream::path($path);
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
        $dir   = $this->getContentOfType($names['dirname'], vfsStreamContent::TYPE_DIR);
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
    public function dir_opendir($path, $options)
    {
        $this->dir = $this->getContentOfType(vfsStream::path($path), vfsStreamContent::TYPE_DIR);
        if (null === $this->dir) {
            return false;
        }
        
        return true;
    }

    /**
     * reads directory contents
     *
     * @return  string
     */
    public function dir_readdir()
    {
        $dir = $this->dir->current();
        if (null === $dir) {
            return false;
        }
        
        $this->dir->next();
        return $dir->getName();
    }

    /**
     * reset directory iteration
     *
     * @return  bool
     */
    public function dir_rewinddir()
    {
        return $this->dir->rewind();
    }

    /**
     * closes directory
     *
     * @return  bool
     */
    public function dir_closedir()
    {
        return true;
    }

    /**
     * returns status of url
     *
     * @param   string  $path  path of url to return status for
     * @return  array
     * @todo    implement correct group and user id handling based on content
     * @todo    implement correct file mode handling based on content
     */
    public function url_stat($path)
    {
        $content = $this->getContent(vfsStream::path($path));
        if (null === $content) {
            return false;
        }
        
        return array(2       => $content->getType() + octdec(0777),
                     4       => 0,
                     5       => 0,
                     7       => $content->size(),
                     9       => $content->filemtime(),
                     'mode'  => $content->getType() + octdec(0777),
                     'uid'   => 0,
                     'gid'   => 0,
                     'size'  => $content->size(),
                     'mtime' => $content->filemtime()
               );
    }
}
?>