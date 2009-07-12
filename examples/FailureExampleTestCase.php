<?php
/**
 * Test case for class FailureExample.
 *
 * @package     bovigo_vfs
 * @subpackage  examples
 * @version     $Id$
 */
require_once 'PHPUnit/Framework.php';
require_once 'vfsStream/vfsStream.php';
require_once 'FailureExample.php';
/**
 * Test case for class FailureExample.
 *
 * @package     bovigo_vfs
 * @subpackage  examples
 */
class FailureExampleTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * set up test environmemt
     */
    public function setUp()
    {
        vfsStreamWrapper::register();
    }

    /**
     * no failure case
     */
    public function testNoFailure()
    {
        $root = new vfsStreamDirectory('exampleDir');
        vfsStreamWrapper::setRoot($root);
        $example = new FailureExample(vfsStream::url('exampleDir/test.txt'));
        $this->assertSame('ok', $example->writeData('testdata'));
        $this->assertTrue($root->hasChild('test.txt'));
        $this->assertSame('testdata', $root->getChild('test.txt')->getContent());
    }

    /**
     * can't write to file
     */
    public function testNoWrite()
    {
        $root = new vfsStreamDirectory('exampleDir');
        $file = $this->getMock('vfsStreamFile', array('write'), array('test.txt'));
        $file->setContent('notoverwritten');
        $file->expects($this->once())
             ->method('write')
             ->will($this->returnValue(false));
        $root->addChild($file);
        vfsStreamWrapper::setRoot($root);
        $example = new FailureExample(vfsStream::url('exampleDir/test.txt'));
        $this->assertSame('could not write data', $example->writeData('testdata'));
        $this->assertTrue($root->hasChild('test.txt'));
        $this->assertSame('notoverwritten', $root->getChild('test.txt')->getContent());
    }
}
?>