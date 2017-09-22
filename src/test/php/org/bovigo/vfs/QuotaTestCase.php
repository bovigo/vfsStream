<?php
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

use function bovigo\assert\assert;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
/**
 * Test for org\bovigo\vfs\Quota.
 *
 * @group  issue_35
 */
class QuotaTestCase extends TestCase
{
    /**
     * instance to test
     *
     * @type  Quota
     */
    private $quota;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->quota = new Quota(10);
    }

    /**
     * @test
     */
    public function unlimitedQuotaIsNotLimited()
    {
        assertFalse(Quota::unlimited()->isLimited());
    }

    /**
     * @test
     */
    public function limitedQuotaIsLimited()
    {
        assertTrue($this->quota->isLimited());
    }

    /**
     * @test
     */
    public function unlimitedQuotaHasAlwaysSpaceLeft()
    {
        assert(Quota::unlimited()->spaceLeft(303), equals(303));
    }

    /**
     * @test
     */
    public function hasNoSpaceLeftWhenUsedSpaceIsLargerThanQuota()
    {
        assert($this->quota->spaceLeft(11), equals(0));
    }

    /**
     * @test
     */
    public function hasNoSpaceLeftWhenUsedSpaceIsEqualToQuota()
    {
        assert($this->quota->spaceLeft(10), equals(0));
    }

    /**
     * @test
     */
    public function hasSpaceLeftWhenUsedSpaceIsLowerThanQuota()
    {
        assert($this->quota->spaceLeft(9), equals(1));
    }
}
