<?php
/**
 * Test for org::bovigo::vfs::vfsStream.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
require_once 'org/bovigo/vfs/vfsStream.php';
require_once 'PHPUnit/Framework.php';
/**
 * Test for org::bovigo::vfs::vfsStream.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 * @since       0.9.0
 * @group       issue_5
 */
class vfsStreamResolveIncludePathTestCase extends PHPUnit_Framework_TestCase
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
        if (version_compare('5.3.2', PHP_VERSION, '>')) {
            $this->markTestSkipped('Test only applies to PHP 5.3.2 or greater.');
        }

        $this->backupIncludePath = get_include_path();
        vfsStream::setup();
        mkdir('vfs://root/a/path', 0777, true);
        set_include_path('vfs://root/a;' . $this->backupIncludePath);
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
        $this->assertFalse(stream_resolve_include_path('path/unknownFile.php'));
    }
}
?>