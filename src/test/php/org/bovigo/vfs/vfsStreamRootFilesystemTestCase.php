<?php
namespace org\bovigo\vfs;

class vfsStreamRootFilesystemTestCase extends \PHPUnit_Framework_TestCase {
    protected function setUp() {
        vfsStream::setup('/');
    }

    public function testRootFilesystemRoundTrip() {
        $file = vfsStream::url('foo');
        $contents = 'bar';

        file_put_contents($file, $contents);

        $this->assertSame($contents, file_get_contents($file));
    }
}
