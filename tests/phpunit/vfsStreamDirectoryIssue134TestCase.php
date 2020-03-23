<?php
/**
 * Created by PhpStorm.
 * Project: vfsStream
 * User: Sebastian Hopfe
 * Date: 14.07.16
 * Time: 14:07
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertNotNull;

/**
 * Test for bovigo\vfs\vfsStreamDirectory.
 *
 * @group  issue_134
 */
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
    public function setUp()
    {
        $this->rootDirectory = vfsStream::newDirectory('/');
        $this->rootDirectory->addChild(vfsStream::newDirectory('var/log/app'));

    }

    /**
     * Test: should save directory name as string internal
     *
     * @small
     */
    public function testShouldSaveDirectoryNameAsStringInternal()
    {
        $dir = $this->rootDirectory->getChild('var/log/app');

        $dir->addChild(vfsStream::newDirectory(80));

        static::assertNotNull($this->rootDirectory->getChild('var/log/app/80'));
    }



    /**
     * Test: should rename directory name as string internal
     *
     * @small
     */
    public function testShouldRenameDirectoryNameAsStringInternal()
    {
        $dir = $this->rootDirectory->getChild('var/log/app');

        $dir->addChild(vfsStream::newDirectory(80));

        $child = $this->rootDirectory->getChild('var/log/app/80');
        $child->rename(90);

        static::assertNotNull($this->rootDirectory->getChild('var/log/app/90'));
    }
}
