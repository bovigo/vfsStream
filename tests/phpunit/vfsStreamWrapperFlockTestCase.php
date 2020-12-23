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
use bovigo\vfs\vfsStreamContainer;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function fclose;
use function file_put_contents;
use function flock;
use function fopen;

use const LOCK_EX;
use const LOCK_NB;
use const LOCK_SH;
use const LOCK_UN;

/**
 * Test for flock() implementation.
 *
 * @see         https://github.com/mikey179/vfsStream/issues/6
 *
 * @since       0.10.0
 * @group       issue_6
 */
class vfsStreamWrapperFlockTestCase extends TestCase
{
    /**
     * root directory
     *
     * @var  vfsStreamContainer
     */
    private $root;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    /**
     * @test
     */
    public function fileIsNotLockedByDefault(): void
    {
        assertFalse(vfsStream::newFile('foo.txt')->isLocked());
    }

    /**
     * @test
     */
    public function streamIsNotLockedByDefault(): void
    {
        file_put_contents(vfsStream::url('root/foo.txt'), 'content');
        assertFalse($this->root->getChild('foo.txt')->isLocked());
    }

    /**
     * @test
     */
    public function canAquireSharedLock(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        assertTrue(flock($fp, LOCK_SH));
        assertTrue($file->isLocked());
        assertTrue($file->hasSharedLock());
        assertFalse($file->hasExclusiveLock());
        fclose($fp);
    }

    /**
     * @test
     */
    public function canAquireSharedLockWithNonBlockingFlockCall(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        assertTrue(flock($fp, LOCK_SH | LOCK_NB));
        assertTrue($file->isLocked());
        assertTrue($file->hasSharedLock());
        assertFalse($file->hasExclusiveLock());
        fclose($fp);
    }

    /**
     * @test
     */
    public function canAquireEclusiveLock(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        assertTrue(flock($fp, LOCK_EX));
        assertTrue($file->isLocked());
        assertFalse($file->hasSharedLock());
        assertTrue($file->hasExclusiveLock());
        fclose($fp);
    }

    /**
     * @test
     */
    public function canAquireEclusiveLockWithNonBlockingFlockCall(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        assertTrue(flock($fp, LOCK_EX | LOCK_NB));
        assertTrue($file->isLocked());
        assertFalse($file->hasSharedLock());
        assertTrue($file->hasExclusiveLock());
        fclose($fp);
    }

    /**
     * @test
     */
    public function canRemoveLock(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp, LOCK_EX);
        assertTrue(flock($fp, LOCK_UN));
        assertFalse($file->isLocked());
        assertFalse($file->hasSharedLock());
        assertFalse($file->hasExclusiveLock());
        fclose($fp);
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_40
     */
    public function canRemoveLockWhenNotLocked(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        assertTrue(flock($fp, LOCK_UN));
        assertFalse($file->isLocked());
        assertFalse($file->hasSharedLock());
        assertFalse($file->hasSharedLock($fp));
        assertFalse($file->hasExclusiveLock());
        assertFalse($file->hasExclusiveLock($fp));
        fclose($fp);
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_40
     */
    public function canRemoveSharedLockWithoutRemovingSharedLockOnOtherFileHandler(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp1 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $fp2 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp1, LOCK_SH);
        $file->lock($fp2, LOCK_SH);
        assertTrue(flock($fp1, LOCK_UN));
        assertTrue($file->hasSharedLock());
        assertFalse($file->hasSharedLock($fp1));
        assertTrue($file->hasSharedLock($fp2));
        fclose($fp1);
        fclose($fp2);
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_40
     */
    public function canNotRemoveSharedLockAcquiredOnOtherFileHandler(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp1 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $fp2 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp1, LOCK_SH);
        assertTrue(flock($fp2, LOCK_UN));
        assertTrue($file->isLocked());
        assertTrue($file->hasSharedLock());
        assertFalse($file->hasExclusiveLock());
        fclose($fp1);
        fclose($fp2);
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_40
     */
    public function canNotRemoveExlusiveLockAcquiredOnOtherFileHandler(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp1 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $fp2 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp1, LOCK_EX);
        assertTrue(flock($fp2, LOCK_UN));
        assertTrue($file->isLocked());
        assertFalse($file->hasSharedLock());
        assertTrue($file->hasExclusiveLock());
        fclose($fp1);
        fclose($fp2);
    }

    /**
     * @test
     */
    public function canRemoveLockWithNonBlockingFlockCall(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp, LOCK_EX);
        assertTrue(flock($fp, LOCK_UN | LOCK_NB));
        assertFalse($file->isLocked());
        assertFalse($file->hasSharedLock());
        assertFalse($file->hasExclusiveLock());
        fclose($fp);
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_40
     */
    public function canNotAquireExclusiveLockIfAlreadyExclusivelyLockedOnOtherFileHandler(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp1 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $fp2 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp1, LOCK_EX);
        assertFalse(flock($fp2, LOCK_EX + LOCK_NB));
        assertTrue($file->isLocked());
        assertFalse($file->hasSharedLock());
        assertTrue($file->hasExclusiveLock());
        assertTrue($file->hasExclusiveLock($fp1));
        assertFalse($file->hasExclusiveLock($fp2));
        fclose($fp1);
        fclose($fp2);
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_40
     */
    public function canAquireExclusiveLockIfAlreadySelfExclusivelyLocked(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp, LOCK_EX);
        assertTrue(flock($fp, LOCK_EX + LOCK_NB));
        assertTrue($file->isLocked());
        assertFalse($file->hasSharedLock());
        assertTrue($file->hasExclusiveLock());
        fclose($fp);
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_40
     */
    public function canNotAquireExclusiveLockIfAlreadySharedLockedOnOtherFileHandler(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp1 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $fp2 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp1, LOCK_SH);
        assertFalse(flock($fp2, LOCK_EX));
        assertTrue($file->isLocked());
        assertTrue($file->hasSharedLock());
        assertFalse($file->hasExclusiveLock());
        fclose($fp1);
        fclose($fp2);
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_40
     */
    public function canAquireExclusiveLockIfAlreadySelfSharedLocked(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp, LOCK_SH);
        assertTrue(flock($fp, LOCK_EX));
        assertTrue($file->isLocked());
        assertFalse($file->hasSharedLock());
        assertTrue($file->hasExclusiveLock());
        fclose($fp);
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_40
     */
    public function canNotAquireSharedLockIfAlreadyExclusivelyLockedOnOtherFileHandler(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp1 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $fp2 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp1, LOCK_EX);
        assertFalse(flock($fp2, LOCK_SH + LOCK_NB));
        assertTrue($file->isLocked());
        assertFalse($file->hasSharedLock());
        assertTrue($file->hasExclusiveLock());
        fclose($fp1);
        fclose($fp2);
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_40
     */
    public function canAquireSharedLockIfAlreadySelfExclusivelyLocked(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp, LOCK_EX);
        assertTrue(flock($fp, LOCK_SH + LOCK_NB));
        assertTrue($file->isLocked());
        assertTrue($file->hasSharedLock());
        assertFalse($file->hasExclusiveLock());
        fclose($fp);
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_40
     */
    public function canAquireSharedLockIfAlreadySelfSharedLocked(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp, LOCK_SH);
        assertTrue(flock($fp, LOCK_SH));
        assertTrue($file->isLocked());
        assertTrue($file->hasSharedLock());
        assertFalse($file->hasExclusiveLock());
        fclose($fp);
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_40
     */
    public function canAquireSharedLockIfAlreadySharedLockedOnOtherFileHandler(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp1 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $fp2 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp1, LOCK_SH);
        assertTrue(flock($fp2, LOCK_SH));
        assertTrue($file->isLocked());
        assertTrue($file->hasSharedLock());
        assertTrue($file->hasSharedLock($fp1));
        assertTrue($file->hasSharedLock($fp2));
        assertFalse($file->hasExclusiveLock());
        fclose($fp1);
        fclose($fp2);
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/31
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_31
     * @group  issue_40
     */
    public function removesExclusiveLockOnStreamClose(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp, LOCK_EX);
        fclose($fp);
        assertFalse($file->isLocked());
        assertFalse($file->hasSharedLock());
        assertFalse($file->hasExclusiveLock());
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/31
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_31
     * @group  issue_40
     */
    public function removesSharedLockOnStreamClose(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp, LOCK_SH);
        fclose($fp);
        assertFalse($file->isLocked());
        assertFalse($file->hasSharedLock());
        assertFalse($file->hasExclusiveLock());
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_40
     */
    public function notRemovesExclusiveLockOnStreamCloseIfExclusiveLockAcquiredOnOtherFileHandler(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp1 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $fp2 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp2, LOCK_EX);
        fclose($fp1);
        assertTrue($file->isLocked());
        assertFalse($file->hasSharedLock());
        assertTrue($file->hasExclusiveLock());
        assertTrue($file->hasExclusiveLock($fp2));
        fclose($fp2);
    }

    /**
     * @see    https://github.com/mikey179/vfsStream/issues/40
     *
     * @test
     * @group  issue_40
     */
    public function notRemovesSharedLockOnStreamCloseIfSharedLockAcquiredOnOtherFileHandler(): void
    {
        $file = vfsStream::newFile('foo.txt')->at($this->root);
        $fp1 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $fp2 = fopen(vfsStream::url('root/foo.txt'), 'rb');
        $file->lock($fp2, LOCK_SH);
        fclose($fp1);
        assertTrue($file->isLocked());
        assertTrue($file->hasSharedLock());
        assertTrue($file->hasSharedLock($fp2));
        assertFalse($file->hasExclusiveLock());
        fclose($fp2);
    }
}
