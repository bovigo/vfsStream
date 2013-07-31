<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  orgbovigovfs
 */
require_once __DIR__ . '/../bootstrap/default.php';
/**
 * Test for orgbovigovfsvfsStreamWrapper.
 *
 * @since  0.9.0
 * @group  issue_3
 */
class vfsStreamWrapperSelectStreamTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException PHPUnit_Framework_Error
     */
    public function selectStream()
    {
        $root = vfsStream::setup();
        $file = vfsStream::newFile('foo.txt')->at($root)->withContent('testContent');

        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $readarray   = array($fp);
        $writearray  = array();
        $exceptarray = array();
        stream_select($readarray, $writearray, $exceptarray, 1);
    }
}
?>
