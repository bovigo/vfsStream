<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
namespace org\bovigo\vfs\content;
/**
 * Default implementation for file contents based on simple strings.
 *
 * @since  1.3.0
 */
class StringBasedFileContent implements FileContent
{
    /**
     * actual content
     *
     * @type  string
     */
    private $content;
    /**
     * current position within content
     *
     * @type  int
     */
    private $offset = 0;

    /**
     * constructor
     *
     * @param  string  $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * returns actual content
     *
     * @return  string
     */
    public function content()
    {
        return $this->content;
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
     * reads the given amount of bytes from content
     *
     * @param   int     $count
     * @return  string
     */
    public function read($count)
    {
        $data = substr($this->content, $this->offset, $count);
        $this->offset += $count;
        return $data;
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
                $this->offset += $offset;
                return true;

            case SEEK_END:
                $this->offset = strlen($this->content) + $offset;
                return true;

            case SEEK_SET:
                $this->offset = $offset;
                return true;

            default:
                return false;
        }

        return false;
    }

    /**
     * checks whether pointer is at end of file
     *
     * @return  bool
     */
    public function eof()
    {
        return $this->offset >= strlen($this->content);
    }

    /**
     * writes an amount of data
     *
     * @param   string  $data
     * @return  amount of written bytes
     */
    public function write($data)
    {
        $dataLength        = strlen($data);
        $this->content     = substr($this->content, 0, $this->offset)
                           . $data
                           . substr($this->content, $this->offset + $dataLength);
        $this->offset += $dataLength;
        return $dataLength;
    }

    /**
     * Truncates a file to a given length
     *
     * @param   int  $size length to truncate file to
     * @return  bool
     */
    public function truncate($size)
    {
        if ($size > $this->size()) {
            // Pad with null-chars if we're "truncating up"
            $this->content .= str_repeat("\0", $size - $this->size());
        } else {
            $this->content = substr($this->content, 0, $size);
        }

        return true;
    }

    public function bytesRead()
    {
        return $this->offset;
    }

    public function readUntilEnd()
    {
        return substr($this->content, $this->offset);
    }
}