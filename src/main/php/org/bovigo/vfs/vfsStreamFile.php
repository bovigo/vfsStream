<?php
/**
 * File container.
 *
 * @package  bovigo_vfs
 * @version  $Id$
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
     * constructor
     *
     * @param  string  $name
     * @param  int     $permissions
     */
    public function __construct($name, $permissions = 0666)
    {
        $this->type = vfsStreamContent::TYPE_FILE;
        parent::__construct($name, $permissions);
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
     * @return  string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * reads the given amount of bytes from content
     *
     * @param   int     $count
     * @return  string
     */
    public function read($count)
    {
        $data = substr($this->content, $this->bytes_read, $count);
        $this->bytes_read += $count;
        return $data;
    }

    /**
     * returns the content until its end from current offset
     *
     * @return  string
     */
    public function readUntilEnd()
    {
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
        $dataLen           = strlen($data);
        $this->content     = substr($this->content, 0, $this->bytes_read) . $data . substr($this->content, $this->bytes_read + $dataLen);
        $this->bytes_read += $dataLen;
        $this->filemtime   = time();
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
}
?>