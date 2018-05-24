<?php

namespace org\bovigo\vfs\test\content\resource;

use org\bovigo\vfs\content\SeekableFileContent;

class SeekableFileContentImplementation extends SeekableFileContent
{

    private $size;

    public function size(): int
    {
        return $this->size;
    }

    public function setSize(int $size)
    {
        $this->size = $size;
    }

    protected function doRead(int $offset, int $count): string
    {
        // functionality not required for testing
    }

    protected function doWrite(string $data, int $offset, int $length)
    {
        // functionality not required for testing
    }

    public function content(): string
    {
        // functionality not required for testing
    }

    public function truncate(int $size): bool
    {
        // functionality not required for testing
    }

}
