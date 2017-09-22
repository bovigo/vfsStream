<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
namespace org\bovigo\vfs;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertTrue;
use function bovigo\assert\assertFalse;
use function bovigo\assert\expect;
/**
 * Test for unlink() functionality.
 *
 * @group  unlink
 */
class UnlinkTestCase extends TestCase
{
    /**
     * @test
     * @group  issue_51
     */
    public function canUnlinkNonWritableFileFromWritableDirectory()
    {
        $structure = ['test_directory' => ['test.file' => '']];
        $root = vfsStream::setup('root', null, $structure);
        $root->getChild('test_directory')->chmod(0777);
        $root->getChild('test_directory')->getChild('test.file')->chmod(0444);
        assertTrue(@unlink(vfsStream::url('root/test_directory/test.file')));
    }

    /**
     * @test
     * @group  issue_51
     */
    public function canNotUnlinkWritableFileFromNonWritableDirectory()
    {
        $structure = ['test_directory' => ['test.file' => '']];
        $root = vfsStream::setup('root', null, $structure);
        $root->getChild('test_directory')->chmod(0444);
        $root->getChild('test_directory')->getChild('test.file')->chmod(0777);
        assertFalse(@unlink(vfsStream::url('root/test_directory/test.file')));
    }

    /**
     * @test
     * @since  1.4.0
     * @group  issue_68
     */
    public function unlinkNonExistingFileTriggersError()
    {
        vfsStream::setup();
        expect(function() { assertFalse(unlink('vfs://root/foo.txt')); })
            ->triggers()
            ->withMessage('unlink(vfs://root/foo.txt): No such file or directory');
    }
}
