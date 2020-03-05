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
use PHPUnit\Framework\TestCase;
use const PATH_SEPARATOR;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
use function file_put_contents;
use function get_include_path;
use function mkdir;
use function set_include_path;
use function stream_resolve_include_path;

/**
 * Test for bovigo\vfs\vfsStream.
 *
 * @since  0.9.0
 * @group  issue_5
 */
class vfsStreamResolveIncludePathTestCase extends \BC_PHPUnit_Framework_TestCase
{
    /**
     * include path to restore after test run
     *
     * @var  string
     */
    protected $backupIncludePath;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->backupIncludePath = get_include_path();
        vfsStream::setup();
        mkdir('vfs://root/a/path', 0777, true);
        set_include_path('vfs://root/a' . PATH_SEPARATOR . $this->backupIncludePath);
    }

    /**
     * clean up test environment
     */
    public function tearDown()
    {
        set_include_path($this->backupIncludePath);
    }

    /**
     * @test
     */
    public function knownFileCanBeResolved()
    {
        file_put_contents('vfs://root/a/path/knownFile.php', '<?php ?>');
        $this->assertEquals('vfs://root/a/path/knownFile.php', stream_resolve_include_path('path/knownFile.php'));
    }

    /**
     * @test
     */
    public function unknownFileCanNotBeResolvedYieldsFalse()
    {
        $this->assertFalse(@stream_resolve_include_path('path/unknownFile.php'));
    }
}
