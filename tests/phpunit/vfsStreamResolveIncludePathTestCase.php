<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertFalse;
use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
use function file_put_contents;
use function get_include_path;
use function mkdir;
use function set_include_path;
use function stream_resolve_include_path;

use const PATH_SEPARATOR;

/**
 * Test for bovigo\vfs\vfsStream.
 *
 * @since  0.9.0
 * @group  issue_5
 */
class vfsStreamResolveIncludePathTestCase extends TestCase
{
    /** @var string */
    protected $backupIncludePath;

    protected function setUp(): void
    {
        $this->backupIncludePath = get_include_path();
        vfsStream::setup();
        mkdir('vfs://root/a/path', 0777, true);
        set_include_path('vfs://root/a' . PATH_SEPARATOR . $this->backupIncludePath);
    }

    protected function tearDown(): void
    {
        set_include_path($this->backupIncludePath);
    }

    /**
     * @test
     */
    public function knownFileCanBeResolved(): void
    {
        file_put_contents('vfs://root/a/path/knownFile.php', '<?php ?>');
        assertThat(
            stream_resolve_include_path('path/knownFile.php'),
            equals('vfs://root/a/path/knownFile.php')
        );
    }

    /**
     * @test
     */
    public function unknownFileCanNotBeResolvedYieldsFalse(): void
    {
        assertFalse(@stream_resolve_include_path('path/unknownFile.php'));
    }
}
