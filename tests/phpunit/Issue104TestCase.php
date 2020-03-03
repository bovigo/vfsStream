<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\vfsStream;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function file_get_contents;

/**
 * @group  issue_104
 * @group  issue_128
 * @since  1.5.0
 */
class Issue104TestCase extends \BC_PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function vfsStreamCanHandleUrlEncodedPathPassedByInternalPhpCode()
    {
        $structure = array('foo bar' => array(
                'schema.xsd' => '<xs:schema targetNamespace="http://www.example.com" xmlns:xs="http://www.w3.org/2001/XMLSchema">
                                    <xs:complexType name="myType"></xs:complexType>
                                </xs:schema>',
                )
        );
        vfsStream::setup('root', null, $structure);
        $doc = new \DOMDocument();
        $this->assertTrue($doc->load(vfsStream::url('root/foo bar/schema.xsd')));
    }

    /**
     * @test
     */
    public function vfsStreamCanHandleUrlEncodedPath()
    {
        $content = '<xs:schema targetNamespace="http://www.example.com" xmlns:xs="http://www.w3.org/2001/XMLSchema">
                                    <xs:complexType name="myType"></xs:complexType>
                                </xs:schema>';
        $structure = array('foo bar' => array(
                'schema.xsd' => $content,
                )
        );
        vfsStream::setup('root', null, $structure);
        $this->assertEquals(
                $content,
                file_get_contents(vfsStream::url('root/foo bar/schema.xsd'))
        );
    }
}
