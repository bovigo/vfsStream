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
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\TestCase;
/**
 * Test for org\bovigo\vfs\vfsStreamDirectory.
 */
class vfsStreamDirectoryTestCase extends TestCase
{
    /**
     * instance to test
     *
     * @var  vfsStreamDirectory
     */
    protected $dir;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->dir = new vfsStreamDirectory('foo');
    }

    /**
     * assure that a directory seperator inside the name throws an exception
     *
     * @test
     * @expectedException  org\bovigo\vfs\vfsStreamException
     */
    public function invalidCharacterInName()
    {
        $dir = new vfsStreamDirectory('foo/bar');
    }

    /**
     * test default values and methods
     *
     * @test
     */
    public function defaultValues()
    {
        $this->assertEquals(vfsStreamContent::TYPE_DIR, $this->dir->getType());
        $this->assertEquals('foo', $this->dir->getName());
        $this->assertTrue($this->dir->appliesTo('foo'));
        $this->assertTrue($this->dir->appliesTo('foo/bar'));
        $this->assertFalse($this->dir->appliesTo('bar'));
        $this->assertEquals(array(), $this->dir->getChildren());
    }

    /**
     * test renaming the directory
     *
     * @test
     */
    public function rename()
    {
        $this->dir->rename('bar');
        $this->assertEquals('bar', $this->dir->getName());
        $this->assertFalse($this->dir->appliesTo('foo'));
        $this->assertFalse($this->dir->appliesTo('foo/bar'));
        $this->assertTrue($this->dir->appliesTo('bar'));
    }

    /**
     * renaming the directory to an invalid name throws a vfsStreamException
     *
     * @test
     * @expectedException  org\bovigo\vfs\vfsStreamException
     */
    public function renameToInvalidNameThrowsvfsStreamException()
    {
        $this->dir->rename('foo/baz');
    }

    /**
     * @test
     * @since  0.10.0
     */
    public function hasNoChildrenByDefault()
    {
        $this->assertFalse($this->dir->hasChildren());
    }

    /**
     * @test
     * @since  0.10.0
     */
    public function hasChildrenReturnsTrueIfAtLeastOneChildPresent()
    {
        $content = NewInstance::of(vfsStreamContent::class)->returns([
            'appliesTo' => false,
            'getName'   => 'baz'
        ]);
        $this->dir->addChild($content);
        $this->assertTrue($this->dir->hasChildren());
    }

    /**
     * @test
     */
    public function hasChildReturnsFalseForNonExistingChild()
    {
        $this->assertFalse($this->dir->hasChild('bar'));
    }

    /**
     * @test
     */
    public function getChildReturnsNullForNonExistingChild()
    {
        $this->assertNull($this->dir->getChild('bar'));
    }

    /**
     * @test
     */
    public function removeChildReturnsFalseForNonExistingChild()
    {
        $this->assertFalse($this->dir->removeChild('bar'));
    }

    /**
     * test that adding, handling and removing of a child works as expected
     *
     * @test
     */
    public function childHandling()
    {
        $content = NewInstance::of(vfsStreamContent::class)->returns([
            'getType'   => vfsStreamContent::TYPE_FILE,
            'appliesTo' => function($name) { return 'bar' === $name; },
            'getName'   => 'bar',
            'size'      => 5
        ]);
        $this->dir->addChild($content);
        $this->assertTrue($this->dir->hasChild('bar'));
        $bar = $this->dir->getChild('bar');
        $this->assertSame($content, $bar);
        $this->assertEquals([$content], $this->dir->getChildren());
        $this->assertEquals(0, $this->dir->size());
        $this->assertEquals(5, $this->dir->sizeSummarized());
        $this->assertTrue($this->dir->removeChild('bar'));
        $this->assertEquals(array(), $this->dir->getChildren());
        $this->assertEquals(0, $this->dir->size());
        $this->assertEquals(0, $this->dir->sizeSummarized());
    }

    /**
     * test that adding, handling and removing of a child works as expected
     *
     * @test
     */
    public function childHandlingWithSubdirectory()
    {
        $content = NewInstance::of(vfsStreamContent::class)->returns([
            'getType'   => vfsStreamContent::TYPE_FILE,
            'getName'   => 'bar',
            'size'      => 5
        ]);
        $subdir = new vfsStreamDirectory('subdir');
        $subdir->addChild($content);
        $this->dir->addChild($subdir);
        $this->assertTrue($this->dir->hasChild('subdir'));
        $this->assertSame($subdir, $this->dir->getChild('subdir'));
        $this->assertEquals([$subdir], $this->dir->getChildren());
        $this->assertEquals(0, $this->dir->size());
        $this->assertEquals(5, $this->dir->sizeSummarized());
        $this->assertTrue($this->dir->removeChild('subdir'));
        $this->assertEquals([], $this->dir->getChildren());
        $this->assertEquals(0, $this->dir->size());
        $this->assertEquals(0, $this->dir->sizeSummarized());
    }

    /**
     * dd
     *
     * @test
     * @group  regression
     * @group  bug_5
     */
    public function addChildReplacesChildWithSameName_Bug_5()
    {
        $content1 = NewInstance::of(vfsStreamContent::class)->returns([
            'getType'   => vfsStreamContent::TYPE_FILE,
            'getName'   => 'bar'
        ]);
        $content2 = NewInstance::of(vfsStreamContent::class)->returns([
            'getType'   => vfsStreamContent::TYPE_FILE,
            'getName'   => 'bar'
        ]);
        $this->dir->addChild($content1);
        $this->assertTrue($this->dir->hasChild('bar'));
        $this->assertSame($content1, $this->dir->getChild('bar'));
        $this->dir->addChild($content2);
        $this->assertTrue($this->dir->hasChild('bar'));
        $this->assertSame($content2, $this->dir->getChild('bar'));
    }

    /**
     * When testing for a nested path, verify that directory separators are respected properly
     * so that subdir1/subdir2 is not considered equal to subdir1Xsubdir2.
     *
     * @test
     * @group bug_24
     * @group regression
     */
    public function explicitTestForSeparatorWithNestedPaths_Bug_24()
    {
        $content = NewInstance::of(vfsStreamContent::class)->returns([
            'getType'   => vfsStreamContent::TYPE_FILE,
            'getName'   => 'bar'
        ]);

        $subdir1 = new vfsStreamDirectory('subdir1');
        $this->dir->addChild($subdir1);

        $subdir2 = new vfsStreamDirectory('subdir2');
        $subdir1->addChild($subdir2);

        $subdir2->addChild($content);

        $this->assertTrue($this->dir->hasChild('subdir1'), "Level 1 path with separator exists");
        $this->assertTrue($this->dir->hasChild('subdir1/subdir2'), "Level 2 path with separator exists");
        $this->assertTrue($this->dir->hasChild('subdir1/subdir2/bar'), "Level 3 path with separator exists");
        $this->assertFalse($this->dir->hasChild('subdir1.subdir2'), "Path with period does not exist");
        $this->assertFalse($this->dir->hasChild('subdir1.subdir2/bar'), "Nested path with period does not exist");
    }


    /**
     * setting and retrieving permissions for a directory
     *
     * @test
     * @group  permissions
     */
    public function permissions()
    {
        $this->assertEquals(0777, $this->dir->getPermissions());
        $this->assertSame($this->dir, $this->dir->chmod(0755));
        $this->assertEquals(0755, $this->dir->getPermissions());
    }

    /**
     * setting and retrieving permissions for a directory
     *
     * @test
     * @group  permissions
     */
    public function permissionsSet()
    {
        $this->dir = new vfsStreamDirectory('foo', 0755);
        $this->assertEquals(0755, $this->dir->getPermissions());
        $this->assertSame($this->dir, $this->dir->chmod(0700));
        $this->assertEquals(0700, $this->dir->getPermissions());
    }

    /**
     * setting and retrieving owner of a file
     *
     * @test
     * @group  permissions
     */
    public function owner()
    {
        $this->assertEquals(vfsStream::getCurrentUser(), $this->dir->getUser());
        $this->assertTrue($this->dir->isOwnedByUser(vfsStream::getCurrentUser()));
        $this->assertSame($this->dir, $this->dir->chown(vfsStream::OWNER_USER_1));
        $this->assertEquals(vfsStream::OWNER_USER_1, $this->dir->getUser());
        $this->assertTrue($this->dir->isOwnedByUser(vfsStream::OWNER_USER_1));
    }

    /**
     * setting and retrieving owner group of a file
     *
     * @test
     * @group  permissions
     */
    public function group()
    {
        $this->assertEquals(vfsStream::getCurrentGroup(), $this->dir->getGroup());
        $this->assertTrue($this->dir->isOwnedByGroup(vfsStream::getCurrentGroup()));
        $this->assertSame($this->dir, $this->dir->chgrp(vfsStream::GROUP_USER_1));
        $this->assertEquals(vfsStream::GROUP_USER_1, $this->dir->getGroup());
        $this->assertTrue($this->dir->isOwnedByGroup(vfsStream::GROUP_USER_1));
    }
}
