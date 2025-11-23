<?php
//declare(strict_types=1);
// disabled as the test requires no strict types
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertNotNull;

/**
 * Test for bovigo\vfs\vfsStreamDirectory.
 *
 * @group  issue_134
 */
#[Group('issue_134')]
#[Small]
class vfsStreamDirectoryIssue134TestCase extends TestCase
{
    /**
     * access to root directory
     *
     * @var  vfsStreamDirectory
     */
    protected $rootDirectory;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $this->rootDirectory = vfsStream::newDirectory('/');
        $this->rootDirectory->addChild(vfsStream::newDirectory('var/log/app'));
    }

    /**
     * @test
     * @small
     */
    #[Test]
    public function shouldSaveDirectoryNameAsStringInternal(): void
    {
        $dir = $this->rootDirectory->getChild('var/log/app');
        $dir->addChild(vfsStream::newDirectory(80));
        assertNotNull($this->rootDirectory->getChild('var/log/app/80'));
    }

    /**
     * @test
     * @small
     */
    #[Test]
    public function shouldRenameDirectoryNameAsStringInternal(): void
    {
        $dir = $this->rootDirectory->getChild('var/log/app');
        $dir->addChild(vfsStream::newDirectory(80));
        $child = $this->rootDirectory->getChild('var/log/app/80');
        $child->rename(90);
        assertNotNull($this->rootDirectory->getChild('var/log/app/90'));
    }
}
