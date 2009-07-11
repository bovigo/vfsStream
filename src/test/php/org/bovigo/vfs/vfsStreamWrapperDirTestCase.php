<?php
/**
 * Test for org::bovigo::vfs::vfsStreamWrapper around mkdir().
 *
 * @package     bovigo_vfs
 * @subpackage  test
 * @version     $Id$
 */
require_once 'org/bovigo/vfs/vfsStream.php';
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/vfsStreamWrapperBaseTestCase.php';
/**
 * Test for org::bovigo::vfs::vfsStreamWrapper around mkdir().
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
class vfsStreamWrapperMkDirTestCase extends vfsStreamWrapperBaseTestCase
{
    /**
     * mkdir() should not overwrite existing root
     *
     * @test
     */
    public function mkdirNoNewRoot()
    {
        $this->assertFalse(mkdir(vfsStream::url('another')));
        $this->assertEquals(2, count($this->foo->getChildren()));
        $this->assertSame($this->foo, vfsStreamWrapper::getRoot());
    }

    /**
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     * @group  permissions
     */
    public function mkdirNonRecursively()
    {
        $this->assertFalse(mkdir($this->barURL . '/another/more'));
        $this->assertEquals(2, count($this->foo->getChildren()));
        $this->assertTrue(mkdir($this->fooURL . '/another'));
        $this->assertEquals(3, count($this->foo->getChildren()));
        $this->assertEquals(0777, $this->foo->getChild('another')->getPermissions());
    }

    /**
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     * @group  permissions
     */
    public function mkdirRecursively()
    {
        $this->assertTrue(mkdir($this->fooURL . '/another/more', 0777, true));
        $this->assertEquals(3, count($this->foo->getChildren()));
        $another = $this->foo->getChild('another');
        $this->assertTrue($another->hasChild('more'));
        $this->assertEquals(0777, $this->foo->getChild('another')->getPermissions());
        $this->assertEquals(0777, $this->foo->getChild('another')->getChild('more')->getPermissions());
    }

    /**
     * no root > new directory becomes root
     *
     * @test
     * @group  permissions
     */
    public function mkdirWithoutRootCreatesNewRoot()
    {
        vfsStreamWrapper::register();
        $this->assertTrue(@mkdir(vfsStream::url('foo')));
        $this->assertEquals(vfsStreamContent::TYPE_DIR, vfsStreamWrapper::getRoot()->getType());
        $this->assertEquals('foo', vfsStreamWrapper::getRoot()->getName());
        $this->assertEquals(0777, vfsStreamWrapper::getRoot()->getPermissions());
    }

    /**
     * trying to create a subdirectory of a file should not work
     *
     * @test
     */
    public function mkdirOnFileReturnsFalse()
    {
        $this->assertFalse(mkdir($this->baz1URL . '/another/more', 0777, true));
    }

    /**
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     * @group  permissions
     */
    public function mkdirNonRecursivelyDifferentPermissions()
    {
        $this->assertTrue(mkdir($this->fooURL . '/another', 0755));
        $this->assertEquals(0755, $this->foo->getChild('another')->getPermissions());
    }

    /**
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     * @group  permissions
     */
    public function mkdirRecursivelyDifferentPermissions()
    {
        $this->assertTrue(mkdir($this->fooURL . '/another/more', 0755, true));
        $this->assertEquals(3, count($this->foo->getChildren()));
        $another = $this->foo->getChild('another');
        $this->assertTrue($another->hasChild('more'));
        $this->assertEquals(0755, $this->foo->getChild('another')->getPermissions());
        $this->assertEquals(0755, $this->foo->getChild('another')->getChild('more')->getPermissions());
    }

    /**
     * assert that mkdir() creates the correct directory structure
     *
     * @test
     * @group  permissions
     */
    public function mkdirUsesParentPermissionsIfNoneGiven()
    {
        $this->foo->chmod(0700);
        $this->assertTrue(mkdir($this->fooURL . '/another/more', null, true));
        $this->assertEquals(3, count($this->foo->getChildren()));
        $another = $this->foo->getChild('another');
        $this->assertTrue($another->hasChild('more'));
        $this->assertEquals(0700, $this->foo->getChild('another')->getPermissions());
        $this->assertEquals(0700, $this->foo->getChild('another')->getChild('more')->getPermissions());
    }

    /**
     * no root > new directory becomes root
     *
     * @test
     * @group  permissions
     */
    public function mkdirWithoutRootCreatesNewRootDifferentPermissions()
    {
        vfsStreamWrapper::register();
        $this->assertTrue(@mkdir(vfsStream::url('foo'), 0755));
        $this->assertEquals(vfsStreamContent::TYPE_DIR, vfsStreamWrapper::getRoot()->getType());
        $this->assertEquals('foo', vfsStreamWrapper::getRoot()->getName());
        $this->assertEquals(0755, vfsStreamWrapper::getRoot()->getPermissions());
    }

    /**
     * no root > new directory becomes root
     *
     * @test
     * @group  permissions
     */
    public function mkdirWithoutRootCreatesNewRootNoPermissions()
    {
        vfsStreamWrapper::register();
        $this->assertTrue(@mkdir(vfsStream::url('foo'), null));
        $this->assertEquals(vfsStreamContent::TYPE_DIR, vfsStreamWrapper::getRoot()->getType());
        $this->assertEquals('foo', vfsStreamWrapper::getRoot()->getName());
        $this->assertEquals(0777, vfsStreamWrapper::getRoot()->getPermissions());
    }

    /**
     * assure that a directory iteration works as expected
     *
     * @test
     */
    public function directoryIteration()
    {
        $dir = dir($this->fooURL);
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            $this->assertTrue('bar' === $entry || 'baz2' === $entry);
        }
        
        $this->assertEquals(2, $i, 'Directory foo contains two children, but got ' . $i . ' children while iterating over directory contents');
        $dir->rewind();
        $i   = 0;
        while (false !== ($entry = $dir->read())) {
            $i++;
            $this->assertTrue('bar' === $entry || 'baz2' === $entry);
        }
        
        $this->assertEquals(2, $i, 'Directory foo contains two children, but got ' . $i . ' children while iterating over directory contents');
        $dir->close();
    }

    /**
     * assure that a directory iteration works as expected
     *
     * @test
     * @group  regression
     * @group  bug_2
     */
    public function directoryIterationWithOpenDir_Bug_2()
    {
        $handle = opendir($this->fooURL);
        $i   = 0;
        while (false !== ($entry = readdir($handle))) {
            $i++;
            $this->assertTrue('bar' === $entry || 'baz2' === $entry);
        }
        
        $this->assertEquals(2, $i, 'Directory foo contains two children, but got ' . $i . ' children while iterating over directory contents');
        
        rewind($handle);
        $i   = 0;
        while (false !== ($entry = readdir($handle))) {
            $i++;
            $this->assertTrue('bar' === $entry || 'baz2' === $entry);
        }
        
        $this->assertEquals(2, $i, 'Directory foo contains two children, but got ' . $i . ' children while iterating over directory contents');
        closedir($handle);
    }

    /**
     * assure that a directory iteration works as expected
     *
     * @author  Christoph Bloemer 
     * @test
     * @group  regression
     * @group  bug_4
     */         
    public function directoryIteration_Bug_4()
    {
        $dir   = $this->fooURL;
        $list1 = array();
        if ($handle = opendir($dir)) {
            while (false !== ($listItem = readdir($handle))) {
                if ('.'  != $listItem && '..' != $listItem) {
                    if (is_file($dir . '/' . $listItem) === true) {
                        $list1[] = 'File:[' . $listItem . ']';
                    } elseif (is_dir($dir . '/' . $listItem) === true) {
                        $list1[] = 'Folder:[' . $listItem . ']';
                    }
                }
            }
            
            closedir($handle);
        }
        
        $list2 = array();
        if ($handle = opendir($dir)) {
            while (false !== ($listItem = readdir($handle))) {
                if ('.'  != $listItem && '..' != $listItem) {
                    if (is_file($dir . '/' . $listItem) === true) {
                        $list2[] = 'File:[' . $listItem . ']';
                    } elseif (is_dir($dir . '/' . $listItem) === true) {
                        $list2[] = 'Folder:[' . $listItem . ']';
                    }
                }
            }
            
            closedir($handle);
        }
        
        $this->assertEquals($list1, $list2);
        $this->assertEquals(2, count($list1));
        $this->assertEquals(2, count($list2));
    }

    /**
     * assure that a directory iteration works as expected
     *
     * @test
     */         
    public function directoryIterationShouldBeIndependent()
    {
        $list1   = array();
        $list2   = array();
        $handle1 = opendir($this->fooURL);
        if (false !== ($listItem = readdir($handle1))) {
            $list1[] = $listItem;
        }
        
        $handle2 = opendir($this->fooURL);
        if (false !== ($listItem = readdir($handle2))) {
            $list2[] = $listItem;
        }
        
        if (false !== ($listItem = readdir($handle1))) {
            $list1[] = $listItem;
        }
        
        if (false !== ($listItem = readdir($handle2))) {
            $list2[] = $listItem;
        }
        
        closedir($handle1);
        closedir($handle2);
        $this->assertEquals($list1, $list2);
        $this->assertEquals(2, count($list1));
        $this->assertEquals(2, count($list2));
    }

    /**
     * assert is_dir() returns correct result
     *
     * @test
     */
    public function is_dir()
    {
        $this->assertTrue(is_dir($this->fooURL));
        $this->assertTrue(is_dir($this->barURL));
        $this->assertFalse(is_dir($this->baz1URL));
        $this->assertFalse(is_dir($this->baz2URL));
        $this->assertFalse(is_dir($this->fooURL . '/another'));
        $this->assertFalse(is_dir(vfsStream::url('another')));
    }

    /**
     * can not unlink without root
     *
     * @test
     */
    public function canNotUnlinkDirectoryWithoutRoot()
    {
        vfsStreamWrapper::register();
        $this->assertFalse(@rmdir(vfsStream::url('foo')));
    }

    /**
     * rmdir() can not remove files
     *
     * @test
     */
    public function rmdirCanNotRemoveFiles()
    {
        $this->assertFalse(rmdir($this->baz1URL));
        $this->assertFalse(rmdir($this->baz2URL));
    }

    /**
     * rmdir() can not remove a non-existing directory
     *
     * @test
     */
    public function rmdirCanNotRemoveNonExistingDirectory()
    {
        $this->assertFalse(rmdir($this->fooURL . '/another'));
    }

    /**
     * rmdir() can not remove non-empty directories
     *
     * @test
     */
    public function rmdirCanNotRemoveNonEmptyDirectory()
    {
        $this->assertFalse(rmdir($this->fooURL));
        $this->assertFalse(rmdir($this->barURL));
    }

    /**
     * rmdir() can remove empty directories
     *
     * @test
     */
    public function rmdirCanRemoveEmptyDirectory()
    {
        vfsStream::newDirectory('empty')->at($this->foo);
        $this->assertTrue($this->foo->hasChild('empty'));
        $this->assertTrue(rmdir($this->fooURL . '/empty'));
        $this->assertFalse($this->foo->hasChild('empty'));
    }

    /**
     * rmdir() can remove empty directories
     *
     * @test
     */
    public function rmdirCanRemoveEmptyRoot()
    {
        $this->foo->removeChild('bar');
        $this->foo->removeChild('baz2');
        $this->assertTrue(rmdir($this->fooURL));
        $this->assertFalse(file_exists($this->fooURL)); // make sure statcache was cleared
        $this->assertNull(vfsStreamWrapper::getRoot());
    }
}
?>