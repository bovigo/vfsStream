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
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
/**
 * @group  issue_104
 * @group  issue_128
 * @since  1.5.0
 */
class Issue104TestCase extends TestCase
{
    private $content;

    public function setup()
    {
      $this->content = '<xs:schema targetNamespace="http://www.example.com" xmlns:xs="http://www.w3.org/2001/XMLSchema">
                          <xs:complexType name="myType"></xs:complexType>
                        </xs:schema>';
      $structure = ['foo bar' => ['schema.xsd' => $this->content]];
      vfsStream::setup('root', null, $structure);
    }

    /**
     * @test
     */
    public function vfsStreamCanHandleUrlEncodedPathPassedByInternalPhpCode()
    {
        $doc = new \DOMDocument();
        assertTrue($doc->load(vfsStream::url('root/foo bar/schema.xsd')));
    }

    /**
     * @test
     */
    public function vfsStreamCanHandleUrlEncodedPath()
    {
        assertThat(
            file_get_contents(vfsStream::url('root/foo bar/schema.xsd')),
            equals($this->content)
        );
    }
}
