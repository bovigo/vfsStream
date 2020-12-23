<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests;

use bovigo\vfs\Quota;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertFalse;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;

/**
 * Test for bovigo\vfs\Quota.
 *
 * @group  issue_35
 */
class QuotaTestCase extends TestCase
{
    /**
     * instance to test
     *
     * @var Quota
     */
    private $quota;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $this->quota = new Quota(10);
    }

    /**
     * @test
     */
    public function unlimitedQuotaIsNotLimited(): void
    {
        assertFalse(Quota::unlimited()->isLimited());
    }

    /**
     * @test
     */
    public function limitedQuotaIsLimited(): void
    {
        assertTrue($this->quota->isLimited());
    }

    /**
     * @test
     */
    public function unlimitedQuotaHasAlwaysSpaceLeft(): void
    {
        assertThat(Quota::unlimited()->spaceLeft(303), equals(303));
    }

    /**
     * @test
     */
    public function hasNoSpaceLeftWhenUsedSpaceIsLargerThanQuota(): void
    {
        assertThat($this->quota->spaceLeft(11), equals(0));
    }

    /**
     * @test
     */
    public function hasNoSpaceLeftWhenUsedSpaceIsEqualToQuota(): void
    {
        assertThat($this->quota->spaceLeft(10), equals(0));
    }

    /**
     * @test
     */
    public function hasSpaceLeftWhenUsedSpaceIsLowerThanQuota(): void
    {
        assertThat($this->quota->spaceLeft(9), equals(1));
    }
}
