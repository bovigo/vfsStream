<?php
/**
 * File container.
 *
 * @package  bovigo_vfs
 */
/**
 * @ignore
 */
require_once dirname(__FILE__) . '/vfsStreamAbstractContent.php';
/**
 * File container.
 *
 * @package  bovigo_vfs
 */
class vfsStreamFile extends vfsStreamAbstractContent
{
    /**
     * the real content of the file
     *
     * @var  string
     */
    protected $content;
    /**
     * amount of read bytes
     *
     * @var  int
     */
    protected $bytes_read = 0;
    /**
     * current lock status of file
     *
     * @var  int
     */
    protected $lock       = LOCK_UN;

    /**
     * constructor
     *
     * @param  string  $name
     * @param  int     $permissions  optional
     */
    public function __construct($name, $permissions = null)
    {
        $this->type = vfsStreamContent::TYPE_FILE;
        parent::__construct($name, $permissions);
    }

    /**
     * returns default permissions for concrete implementation
     *
     * @return  int
     * @since   0.8.0
     */
    protected function getDefaultPermissions()
    {
        return 0666;
    }

    /**
     * checks whether the container can be applied to given name
     *
     * @param   string  $name
     * @return  bool
     */
    public function appliesTo($name)
    {
        return ($name === $this->name);
    }

    /**
     * alias for withContent()
     *
     * @param   string  $content
     * @return  vfsStreamFile
     * @see     withContent()
     */
    public function setContent($content)
    {
        return $this->withContent($content);
    }

    /**
     * sets the contents of the file
     *
     * Setting content with this method does not change the time when the file
     * was last modified.
     *
     * @param   string  $content
     * @return  vfsStreamFile
     * @see     setContent()
     */
    public function withContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * returns the contents of the file
     *
     * Getting content does not change the time when the file
     * was last accessed.
     *
     * @return  string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * simply open the file
     *
     * @since  0.9
     */
    public function open()
    {
        $this->seek(0, SEEK_SET);
        $this->updateModifications();
    }

    /**
     * open file and set pointer to end of file
     *
     * @since  0.9
     */
    public function openForAppend()
    {
        $this->seek(0, SEEK_END);
        $this->updateModifications();
    }

    /**
     * open file and truncate content
     *
     * @since  0.9
     */
    public function openWithTruncate()
    {
        $this->seek(0, SEEK_SET);
        $this->content = '';
        $this->updateModifications(true);
    }

    /**
     * updates internal timestamps
     *
     * @since  0.9
     */
    protected function updateModifications($written = false)
    {
        $time = time();
        $this->lastAccessed = $time;
        if ($written)
            $this->lastModified = $time;
    }

    /**
     * reads the given amount of bytes from content
     *
     * Using this method changes the time when the file was last accessed.
     *
     * @param   int     $count
     * @return  string
     */
    public function read($count)
    {
        $data = substr($this->content, $this->bytes_read, $count);
        $this->bytes_read  += $count;
        $this->lastAccessed = time();
        return $data;
    }

    /**
     * returns the content until its end from current offset
     *
     * Using this method changes the time when the file was last accessed.
     *
     * @return  string
     */
    public function readUntilEnd()
    {
        $this->lastAccessed = time();
        return substr($this->content, $this->bytes_read);
    }

    /**
     * writes an amount of data
     *
     * Using this method changes the time when the file was last modified.
     *
     * @param   string  $data
     * @return  amount of written bytes
     */
    public function write($data)
    {
        $dataLen            = strlen($data);
        $this->content      = substr($this->content, 0, $this->bytes_read) . $data . substr($this->content, $this->bytes_read + $dataLen);
        $this->bytes_read  += $dataLen;
        $this->lastModified = time();
        return $dataLen;
    }

    /**
     * checks whether pointer is at end of file
     *
     * @return  bool
     */
    public function eof()
    {
        return $this->bytes_read >= strlen($this->content);
    }

    /**
     * returns the current position within the file
     *
     * @return  int
     */
    public function getBytesRead()
    {
        return $this->bytes_read;
    }

    /**
     * seeks to the given offset
     *
     * @param   int   $offset
     * @param   int   $whence
     * @return  bool
     */
    public function seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_CUR:
                $this->bytes_read += $offset;
                return true;
            
            case SEEK_END:
                $this->bytes_read = strlen($this->content) + $offset;
                return true;
            
            case SEEK_SET:
                $this->bytes_read = $offset;
                return true;
            
            default:
                return false;
        }
        
        return false;
    }

    /**
     * returns size of content
     *
     * @return  int
     */
    public function size()
    {
        return strlen($this->content);
    }


    /**
     * locks file for
     *
     * @param   int            $operation
     * @return  vfsStreamFile
     * @since   0.10.0
     * @see     https://github.com/mikey179/vfsStream/issues/6
     */
    public function lock($operation)
    {
        if ((LOCK_NB & $operation) == LOCK_NB) {
            $this->lock = $operation - LOCK_NB;
        } else {
            $this->lock = $operation;
        }
        
        return $this;
    }

    /**
     * checks whether file is locked
     *
     * @return  bool
     * @since   0.10.0
     * @see     https://github.com/mikey179/vfsStream/issues/6
     */
    public function isLocked()
    {
        return (LOCK_UN !== $this->lock);
    }

    /**
     * checks whether file is locked in shared mode
     *
     * @return  bool
     * @since   0.10.0
     * @see     https://github.com/mikey179/vfsStream/issues/6
     */
    public function hasSharedLock()
    {
        return (LOCK_SH === $this->lock);
    }

    /**
     * checks whether file is locked in exclusive mode
     *
     * @return  bool
     * @since   0.10.0
     * @see     https://github.com/mikey179/vfsStream/issues/6
     */
    public function hasExclusiveLock()
    {
        return (LOCK_EX === $this->lock);
    }
}
?>
