<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs;

use function class_alias;

/**
 * Represents a quota for disk space.
 *
 * @internal
 *
 * @since     1.1.0
 */
class Quota
{
    /**
     * unlimited quota
     */
    public const UNLIMITED = -1;
    /**
     * quota in bytes
     *
     * A value of -1 is treated as unlimited.
     *
     * @var  int
     */
    private $amount;

    /**
     * constructor
     *
     * @param  int $amount quota in bytes
     */
    public function __construct(int $amount)
    {
        $this->amount = $amount;
    }

    /**
     * create with unlimited space
     */
    public static function unlimited(): self
    {
        return new self(self::UNLIMITED);
    }

    /**
     * checks if a quota is set
     */
    public function isLimited(): bool
    {
        return self::UNLIMITED < $this->amount;
    }

    /**
     * checks if given used space exceeda quota limit
     */
    public function spaceLeft(int $usedSpace): int
    {
        if ($this->amount === self::UNLIMITED) {
            return $usedSpace;
        }

        if ($usedSpace >= $this->amount) {
            return 0;
        }

        $spaceLeft = $this->amount - $usedSpace;
        if (0 >= $spaceLeft) {
            return 0;
        }

        return $spaceLeft;
    }
}

class_alias('bovigo\vfs\Quota', 'org\bovigo\vfs\Quota');
