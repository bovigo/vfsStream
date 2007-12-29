<?php
/**
 * Stream wrapper to mock file system requests.
 *
 * @author      Frank Kleine <mikey@stubbles.net>
 * @package     stubbles_vfs
 */
require_once dirname(__FILE__) . '/vfsStreamDirectory.php';
require_once dirname(__FILE__) . '/vfsStreamFile.php';
require_once dirname(__FILE__) . '/vfsStreamException.php';
/**
 * Stream wrapper to mock file system requests.
 *
 * @package     stubbles_vfs
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
        if (true == self::$registered) {
            return;
        }

        if (stream_wrapper_register(vfsStream::SCHEME, __CLASS__) == false) {
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
        if (null === self::$root) {
            return false;
        }
        
        $path = vfsStream::path($path);
        if (self::$root->getName() === $path) {
            $this->content = self::$root;
        } elseif (self::$root->hasChild($path) === false) {
            // next step depends on $mode value
            $lastSlashPos = strrpos($path, '/');
            $subPath      = substr($path, 0, $lastSlashPos);
            if (self::$root->getName() === $subPath) {
                $dir = self::$root;
            } elseif (self::$root->hasChild($subPath) === true) {
                $dir = self::$root->getChild($subPath);
            } else {
                return false;
            }
            
            if ($dir->getType() !== vfsStreamContent::TYPE_DIR) {
                return false;
            }
            
            $this->content = new vfsStreamFile(substr($path, $lastSlashPos + 1));
            $dir->addChild($this->content);
        } else {
            $this->content = self::$root->getChild($path);
        }
        
        if ($this->content->getType() !== vfsStreamContent::TYPE_FILE) {
            return false;
        }
        
        // FIXME!!! $mode not evaluated here
        $this->content->seek(0, SEEK_SET);
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
        if (null === self::$root) {
            return false;
        }
        
        $realPath = vfsStream::path($path);
        if (self::$root->getName() === $realPath) {
            // delete root? very brave. :)
            self::$root = null;
            clearstatcache();
            return true;
        } elseif (self::$root->hasChild($realPath) === false) {
            return false;
        }
        
        $subPath = substr($realPath, 0, strrpos($realPath, '/'));
        if (self::$root->getName() === $subPath) {
            $content = self::$root;
        } else {
            $content = self::$root->getChild($subPath);
        }
        
        clearstatcache();
        return $content->removeChild(substr($realPath, strrpos($realPath, '/') + 1));
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
        if (null === self::$root) {
            return false;
        }
        
        return false;
    }

    /**
     * creates a new directory
     *
     * @param   string  $path
     * @param   int     $mode
     * @param   int     $options
     * @return  bool
     */
    public function mkdir($path, $mode, $options)
    {
        if (null === self::$root) {
            return false;
        }
        
        $path         = vfsStream::path($path);
        $lastSlashPos = strrpos($path, '/');
        $recursive    = ((STREAM_MKDIR_RECURSIVE & $options) !== 0) ? (true) : (false);
        try {
            $this->createDir(substr($path, 0, $lastSlashPos), substr($path, $lastSlashPos + 1), $recursive);
        } catch (vfsStreamException $stse) {
            return false;
        }
        
        return true;
    }

    /**
     * helper method to create a directory recursively
     *
     * @param   string  $subPath    the path where to create directory in
     * @param   string  $directory  the name of the directory to create
     * @param   bool    $recursive  whether recursive creation is allowed or not
     * @param   int     $depth      depth of recursion
     * @throws  vfsStreamException
     */
    protected function createDir($subPath, $directory, $recursive, $depth = 0)
    {
        $dir = null;
        if (self::$root->getName() === $subPath) {
            $dir = self::$root;
        } elseif (self::$root->hasChild($subPath) === true) {
            $dir = self::$root->getChild($subPath);
        }
        
        if (null === $dir) {
            $lastSlashPos = strrpos($subPath, '/');
            if (false === $lastSlashPos) {
                throw new vfsStreamException('Creation of new directory ' . $directory . ' failed, could not find ' . $subPath);
            }
            
            $this->createDir(substr($subPath, 0, $lastSlashPos), substr($subPath, $lastSlashPos + 1), $recursive, $depth + 1);
            if (self::$root->hasChild($subPath) === false) {
                throw new vfsStreamException('Creation of new directory ' . $directory . ' failed, could not find ' . $subPath);
            }
            
            $dir = self::$root->getChild($subPath);
        }
        
        if ($dir->getType() !== vfsStreamContent::TYPE_DIR) {
            throw new vfsStreamException('Creation of new directory ' . $directory . ' failed, ' . $subPath . ' is not a directory.');
        } elseif (false === $recursive && 0 < $depth) {
            throw new vfsStreamException('Creation of new directory ' . $directory . ' failed, can not create recursively.');
        }
        
        $dir->addChild(new vfsStreamDirectory($directory));
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
        if (null === self::$root) {
            return false;
        }
        
        return false;
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
        if (null === self::$root) {
            return false;
        }
        
        $path = vfsStream::path($path);
        if (self::$root->getName() === $path) {
            $this->dir = self::$root;
        } elseif (self::$root->hasChild($path) === false) {
            return false;
        } else {            
            $this->dir = self::$root->getChild($path);
        }
        
        if ($this->dir->getType() !== vfsStreamContent::TYPE_DIR) {
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
        if (null === self::$root) {
            return false;
        }
        
        $path = vfsStream::path($path);
        if (self::$root->getName() === $path) {
            $content = self::$root;
        } elseif (self::$root->hasChild($path) === false) {
            return false;
        } else {
            $content = self::$root->getChild($path);
        }
        
        return array(2       => $content->getType() + octdec(0777),
                     4       => 0,
                     5       => 0,
                     7       => (($content->getType() !== vfsStreamContent::TYPE_DIR) ? ($content->size()) : (0)),
                     9       => $content->filemtime(),
                     'mode'  => $content->getType() + octdec(0777),
                     'uid'   => 0,
                     'gid'   => 0,
                     'size'  => (($content->getType() !== vfsStreamContent::TYPE_DIR) ? ($content->size()) : (0)),
                     'mtime' => $content->filemtime()
               );
    }
}
?>