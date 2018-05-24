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

use org\bovigo\vfs\test\content\resource\SeekableFileContentImplementation;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\isGreaterThanOrEqualTo;

/**
 * Test for org\bovigo\vfs\content\SeekableFileContent.
 *
 * @since  1.6.6
 * @group  issue_169
 */
class SeekableFileContentTestCase extends TestCase
{

    /**
     * @test
     */
    public function cannotReturnOffsetPastEof()
    {

        $size = 100;

        $file = new SeekableFileContentImplementation();
        $file->setSize($size);

        $file->seek($size + 10, SEEK_SET);

        assertThat($file->bytesRead(), isGreaterThanOrEqualTo($size));

    }

}

