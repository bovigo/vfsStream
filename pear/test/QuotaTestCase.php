<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
require_once __DIR__ . '/../bootstrap/default.php';
/**
 * Test for org\bovigo\vfs\Quota.
 *
 * @group  issue_35
 */
class QuotaTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  vfsStream_Quota
     */
    private $quota;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->quota = new vfsStream_Quota(10);
    }

    /**
     * @test
     */
    public function unlimitedQuotaIsNotLimited()
    {
        $this->assertFalse(vfsStream_Quota::unlimited()->isLimited());
    }

    /**
     * @test
     */
    public function limitedQuotaIsLimited()
    {
        $this->assertTrue($this->quota->isLimited());
    }

    /**
     * @test
     */
    public function unlimitedQuotaHasAlwaysSpaceLeft()
    {
        $this->assertEquals(303, vfsStream_Quota::unlimited()->spaceLeft(303));
    }

    /**
     * @test
     */
    public function hasNoSpaceLeftWhenUsedSpaceIsLargerThanQuota()
    {
        $this->assertEquals(0, $this->quota->spaceLeft(11));
    }

    /**
     * @test
     */
    public function hasNoSpaceLeftWhenUsedSpaceIsEqualToQuota()
    {
        $this->assertEquals(0, $this->quota->spaceLeft(10));
    }

    /**
     * @test
     */
    public function hasSpaceLeftWhenUsedSpaceIsLowerThanQuota()
    {
        $this->assertEquals(1, $this->quota->spaceLeft(9));
    }
}
?>
