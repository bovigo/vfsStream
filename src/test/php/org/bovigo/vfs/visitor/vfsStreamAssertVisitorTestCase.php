<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
namespace org\bovigo\vfs\visitor;
use org\bovigo\vfs\vfsStream;

/**
 * Test for org\bovigo\vfs\visitor\vfsStreamAssertVisitor
 */
class vfsStreamAssertVisitorTestCase extends \PHPUnit_Framework_TestCase {
	/**
	 * @test
	 */
	public function visitRecursiveDirectoryStructure() {
		$root             = vfsStream::setup('root',
			null,
			[
				'test'    => [
					'foo'     => ['test.txt' => 'hello'],
					'baz.txt' => 'world'
				],
				'foo.txt' => ''
			]
		);
		$structureVisitor = new vfsStreamAssertVisitor();

		$expected = <<<EOF
\=root @777
.\=test @777
..\=foo @777
...\-test.txt @666
..\-baz.txt @666
.\-foo.txt @666
EOF;

		$this->assertEquals(
			$expected,
			$structureVisitor->visitDirectory($root)->getStructure()
		);
	}
}

?>
