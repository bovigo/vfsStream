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
namespace org\bovigo\vfs;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertEmptyArray;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isSameAs;
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
        $this->dir = vfsStream::newDirectory('foo');
    }

    /**
     * @test
     */
    public function invalidCharacterInNameThrowsException()
    {
        expect(function() { new vfsStreamDirectory('foo/bar'); })
            ->throws(vfsStreamException::class);
    }

    /**
     * @test
     */
    public function isOfTypeDir()
    {
        assertThat($this->dir->getType(), equals(vfsStreamContent::TYPE_DIR));
    }

    /**
     * @test
     */
    public function appliesForSelf()
    {
        assertTrue($this->dir->appliesTo('foo'));
    }

    /**
     * @test
     */
    public function appliesForSubDirectories()
    {
        assertTrue($this->dir->appliesTo('foo/bar'));
    }

    /**
     * @test
     */
    public function doesNotApplyForOtherNames()
    {
        assertFalse($this->dir->appliesTo('bar'));
    }

    /**
     * @test
     */
    public function hasGivenName()
    {
        assertThat($this->dir->getName(), equals('foo'));
    }

    /**
     * @test
     */
    public function canBeRenamed()
    {
        $this->dir->rename('bar');
        assertThat($this->dir->getName(), equals('bar'));
        assertFalse($this->dir->appliesTo('foo'));
        assertFalse($this->dir->appliesTo('foo/bar'));
        assertTrue($this->dir->appliesTo('bar'));
    }

    /**
     * @test
     */
    public function renameToInvalidNameThrowsvfsStreamException()
    {
        expect(function() { $this->dir->rename('foo/baz'); })
            ->throws(vfsStreamException::class);
    }

    /**
     * @test
     * @since  0.10.0
     */
    public function hasNoChildrenByDefault()
    {
        assertFalse($this->dir->hasChildren());
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
        assertTrue($this->dir->hasChildren());
    }

    /**
     * @test
     */
    public function hasChildReturnsFalseForNonExistingChild()
    {
        assertFalse($this->dir->hasChild('bar'));
    }

    /**
     * @test
     */
    public function getChildReturnsNullForNonExistingChild()
    {
        assertNull($this->dir->getChild('bar'));
    }

    /**
     * @test
     */
    public function removeChildReturnsFalseForNonExistingChild()
    {
        assertFalse($this->dir->removeChild('bar'));
    }

    private function createChild(): vfsStreamContent
    {
      return NewInstance::of(vfsStreamContent::class)->returns([
          'getType'   => vfsStreamContent::TYPE_FILE,
          'appliesTo' => function($name) { return 'bar' === $name; },
          'getName'   => 'bar',
          'size'      => 5
      ]);
    }

    /**
     * @test
     */
    public function hasChildAfterItHasBeenAdded()
    {
        $this->dir->addChild($this->createChild());
        assertTrue($this->dir->hasChild('bar'));
    }

    /**
     * @test
     */
    public function returnsAddedInstance()
    {
        $content = $this->createChild();
        $this->dir->addChild($content);
        assertThat($this->dir->getChild('bar'), isSameAs($content));
    }

    /**
     * @test
     */
    public function returnsListOfAll()
    {
        $content = $this->createChild();
        $this->dir->addChild($content);
        assertThat($this->dir->getChildren(), equals([$content]));
    }

    /**
     * @test
     */
    public function sizeOfDirectoryIs0()
    {
        assertThat($this->dir->size(), equals(0));
    }

    /**
     * @test
     */
    public function sizeOfDirectoryIsAlways0()
    {
        $this->dir->addChild($this->createChild());
        assertThat($this->dir->size(), equals(0));
    }

    /**
     * @test
     */
    public function summarizedSizeIs0WhenNoChildrenAdded()
    {
        assertThat($this->dir->sizeSummarized(), equals(0));
    }

    /**
     * @test
     */
    public function summarizedSizeContainsSizeOfChildren()
    {
        $this->dir->addChild($this->createChild());
        assertThat($this->dir->sizeSummarized(), equals(5));
    }

    /**
     * @test
     */
    public function summarizedSizeContainsSizeOfChildrenIncludingSubdirectories()
    {
        $subdir  = vfsStream::newDirectory('subdir');
        $subdir->addChild($this->createChild());
        $this->dir->addChild($subdir);
        assertThat($this->dir->sizeSummarized(), equals(5));
    }

    /**
     * @test
     */
    public function childCanBeRemoved()
    {
        $this->dir->addChild($this->createChild());
        assertTrue($this->dir->removeChild('bar'));
        assertEmptyArray($this->dir->getChildren());
    }

    /**
     * @test
     * @group  regression
     * @group  bug_5
     */
    public function addChildReplacesChildWithSameName_Bug_5()
    {
        $content2 = $this->createChild();
        $this->dir->addChild($this->createChild());
        $this->dir->addChild($content2);
        assertThat($this->dir->getChild('bar'), isSameAs($content2));
    }

    /**
     * When testing for a nested path, verify that directory separators are
     * respected properly so that subdir1/subdir2 is not considered equal to
     * subdir1Xsubdir2.
     *
     * @test
     * @group bug_24
     * @group regression
     */
    public function explicitTestForSeparatorWithNestedPaths_Bug_24()
    {
        $subdir1 = vfsStream::newDirectory('subdir1');
        $this->dir->addChild($subdir1);
        $subdir2 = vfsStream::newDirectory('subdir2');
        $subdir1->addChild($subdir2);
        $subdir2->addChild($this->createChild());

        assertTrue($this->dir->hasChild('subdir1'), 'Level 1 path with separator exists');
        assertTrue($this->dir->hasChild('subdir1/subdir2'), 'Level 2 path with separator exists');
        assertTrue($this->dir->hasChild('subdir1/subdir2/bar'), 'Level 3 path with separator exists');
        assertFalse($this->dir->hasChild('subdir1.subdir2'), 'Path with period does not exist');
        assertFalse($this->dir->hasChild('subdir1.subdir2/bar'), 'Nested path with period does not exist');
    }

    /**
     * @test
     * @group  permissions
     */
    public function defaultPermissions()
    {
        assertThat($this->dir->getPermissions(), equals(0777));
    }


    /**
     * @test
     * @group  permissions
     */
    public function permissionsCanBeChanged()
    {
        assertThat($this->dir->chmod(0755)->getPermissions(), equals(0755));
    }

    /**
     * @test
     * @group  permissions
     */
    public function permissionsCanBeSetOnCreation()
    {
        assertThat(vfsStream::newDirectory('foo', 0755)->getPermissions(), equals(0755));
    }

    /**
     * @test
     * @group  permissions
     */
    public function currentUserIsDefaultOwner()
    {
        assertThat($this->dir->getUser(), equals(vfsStream::getCurrentUser()));
        assertTrue($this->dir->isOwnedByUser(vfsStream::getCurrentUser()));
    }

    /**
     * @test
     * @group  permissions
     */
    public function ownerCanBeChanged()
    {
        $this->dir->chown(vfsStream::OWNER_USER_1);
        assertThat($this->dir->getUser(), equals(vfsStream::OWNER_USER_1));
        assertTrue($this->dir->isOwnedByUser(vfsStream::OWNER_USER_1));
    }

    /**
     * @test
     * @group  permissions
     */
    public function currentGroupIsDefaultGroup()
    {
        assertThat($this->dir->getGroup(), equals(vfsStream::getCurrentGroup()));
        assertTrue($this->dir->isOwnedByGroup(vfsStream::getCurrentGroup()));
    }

    /**
     * @test
     * @group  permissions
     */
    public function groupCanBeChanged()
    {
        $this->dir->chgrp(vfsStream::GROUP_USER_1);
        assertThat($this->dir->getGroup(), equals(vfsStream::GROUP_USER_1));
        assertTrue($this->dir->isOwnedByGroup(vfsStream::GROUP_USER_1));
    }
}
