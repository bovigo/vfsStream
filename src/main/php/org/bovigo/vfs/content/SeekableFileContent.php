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
abstract class SeekableFileContent implements FileContent
{
    /**
     * current position within content
     *
     * @type  int
     */
    private $offset = 0;

    /**
     * reads the given amount of bytes from content
     *
     * @param   int     $count
     * @return  string
     */
    public function read(int $count): string
    {
        $data = $this->doRead($this->offset, $count);
        $this->offset += $count;
        return $data;
    }

    /**
     * actual reading of given byte count starting at given offset
     *
     * @param   int  $offset
     * @param   int  $count
     * @return  string
     */
    protected abstract function doRead(int $offset, int $count): string;

    /**
     * seeks to the given offset
     *
     * @param   int   $offset
     * @param   int   $whence
     * @return  bool
     */
    public function seek(int $offset, int $whence): bool
    {
        $newOffset = $this->offset;
        $size = $this->size();

        switch ($whence) {
            case SEEK_CUR:
                $newOffset += $offset;
                break;

            case SEEK_END:
                $newOffset = $size + $offset;
                break;

            case SEEK_SET:
                $newOffset = $offset;
                break;

            default:
                return false;
        }

        if ($newOffset > $size) {
            $newOffset = $size;
        }


        if ($newOffset < 0) {
            return false;
        }

        $this->offset = $newOffset;
        return true;
    }

    /**
     * checks whether pointer is at end of file
     *
     * @return  bool
     */
    public function eof(): bool
    {
        return $this->size() <= $this->offset;
    }

    /**
     * writes an amount of data
     *
     * @param   string  $data
     * @return  amount of written bytes
     */
    public function write(string $data): int
    {
        $dataLength    = strlen($data);
        $this->doWrite($data, $this->offset, $dataLength);
        $this->offset += $dataLength;
        return $dataLength;
    }

    /**
     * actual writing of data with specified length at given offset
     *
     * @param   string  $data
     * @param   int     $offset
     * @param   int     $length
     */
    protected abstract function doWrite(string $data, int $offset, int $length);

    /**
     * for backwards compatibility with vfsStreamFile::bytesRead()
     *
     * @return  int
     * @deprecated
     */
    public function bytesRead(): int
    {
        return $this->offset;
    }

    /**
     * for backwards compatibility with vfsStreamFile::readUntilEnd()
     *
     * @return  string
     * @deprecated
     */
    public function readUntilEnd(): string
    {
        $data = substr($this->content(), $this->offset);
        return (false === $data) ? '' : $data;
    }
}
