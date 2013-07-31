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
 * Test for vfsvfsStream.
 *
 * @since       0.9.0
 * @group       issue_2
 */
class vfsStreamGlobTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function globDoesNotWorkWithVfsStreamUrls()
    {
        $root = vfsStream::setup('example');
        mkdir(vfsStream::url('example/test/'), 0777, true);
        $this->assertEmpty(glob(vfsStream::url('example'), GLOB_MARK));
    }
}
?>
