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
namespace org\bovigo\vfs\content;
/**
 * Default implementation for file contents based on simple strings.
 *
 * @since  1.3.0
 */
class StringBasedFileContent extends SeekableFileContent implements FileContent
{
    /**
     * actual content
     *
     * @type  string
     */
    private $content;

    /**
     * constructor
     *
     * @param  string  $content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * returns actual content
     *
     * @return  string
     */
    public function content(): string
    {
        return $this->content;
    }

    /**
     * returns size of content
     *
     * @return  int
     */
    public function size(): int
    {
        return strlen($this->content);
    }

    /**
     * actual reading of length starting at given offset
     *
     * @param   int  $offset
     * @param   int  $count
     * @return  string
     */
    protected function doRead(int $offset, int $count): string
    {
        $data = substr($this->content, $offset, $count);
        return (false === $data) ? '' : $data;
    }

    /**
     * actual writing of data with specified length at given offset
     *
     * @param   string  $data
     * @param   int     $offset
     * @param   int     $length
     */
    protected function doWrite(string $data, int $offset, int $length)
    {
        $this->content = substr($this->content, 0, $offset)
                       . $data
                       . substr($this->content, $offset + $length);
    }

    /**
     * Truncates a file to a given length
     *
     * @param   int  $size length to truncate file to
     * @return  bool
     */
    public function truncate(int $size): bool
    {
        if ($size > $this->size()) {
            // Pad with null-chars if we're "truncating up"
            $this->content .= str_repeat("\0", $size - $this->size());
        } else {
            $this->content = substr($this->content, 0, $size);
        }

        return true;
    }
}
