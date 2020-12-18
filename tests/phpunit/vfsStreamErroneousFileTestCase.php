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
use bovigo\vfs\vfsStreamErroneousFile;

use function bovigo\assert\assertEmptyString;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function rand;
use function uniqid;

use const E_USER_WARNING;

/**
 * Test for bovigo\vfs\vfsStreamErroneousFile.
 */
class vfsStreamErroneousFileTestCase extends vfsStreamFileTestCase
{
    /**
     * instance to test
     *
     * @var vfsStreamErroneousFile
     */
    protected $file;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $this->file = vfsStream::newErroneousFile('foo', []);
    }

    public function testOpenWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['open' => $message]);

        expect(static function () use ($file): void {
            $file->open();
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testOpenForAppendWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['open' => $message]);

        expect(static function () use ($file): void {
            $file->openForAppend();
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testOpenWithTruncateWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['open' => $message]);

        expect(static function () use ($file): void {
            $file->openWithTruncate();
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testReadWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['read' => $message]);

        expect(static function () use ($file): void {
            $file->read(rand());
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testReadWithErrorMessageReturnsEmptyString(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['read' => uniqid()]);

        $actual = @$file->read(rand());

        assertEmptyString($actual);
    }

    public function testReadUntilEndWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['read' => $message]);

        expect(static function () use ($file): void {
            $file->readUntilEnd(rand());
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testReadUntilEndWithErrorMessageReturnsEmptyString(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['read' => uniqid()]);

        $actual = @$file->readUntilEnd(rand());

        assertEmptyString($actual);
    }

    public function testWriteWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['write' => $message]);

        expect(static function () use ($file): void {
            $file->write(uniqid());
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testWriteWithErrorMessageReturnsZero(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['write' => uniqid()]);

        $actual = @$file->write(uniqid());

        assertThat($actual, equals(0));
    }

    public function testTruncateWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['truncate' => $message]);

        expect(static function () use ($file): void {
            $file->truncate(rand());
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testTruncateWithErrorMessageReturnsFalse(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['truncate' => uniqid()]);

        $actual = @$file->truncate(rand());

        assertFalse($actual);
    }

    public function testEofWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['eof' => $message]);

        expect(static function () use ($file): void {
            $file->eof();
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testEofWithErrorMessageReturnsTrue(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['eof' => uniqid()]);

        $actual = @$file->eof();

        assertTrue($actual);
    }

    public function testGetBytesReadWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['tell' => $message]);

        expect(static function () use ($file): void {
            $file->getBytesRead();
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testGetBytesReadWithErrorMessageReturnsZero(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['tell' => uniqid()]);

        $actual = @$file->getBytesRead();

        assertThat($actual, equals(0));
    }

    public function testSeekWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['seek' => $message]);

        expect(static function () use ($file): void {
            $file->seek(rand(), rand());
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testSeekWithErrorMessageReturnsFalse(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['seek' => uniqid()]);

        $actual = @$file->seek(rand(), rand());

        assertFalse($actual);
    }

    public function testSizeWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['stat' => $message]);

        expect(static function () use ($file): void {
            $file->size();
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testSizeWithErrorMessageReturnsNegativeOne(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['stat' => uniqid()]);

        $actual = @$file->size();

        assertThat($actual, equals(-1));
    }

    public function testLockWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['lock' => $message]);

        expect(static function () use ($file): void {
            $file->lock($file, rand());
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testLockWithErrorMessageReturnsFalse(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['lock' => uniqid()]);

        $actual = @$file->lock($file, rand());

        assertFalse($actual);
    }

    public function testFilemtimeWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['stat' => $message]);

        expect(static function () use ($file): void {
            $file->filemtime();
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testFilemtimeWithErrorMessageReturnsNegativeOne(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['stat' => uniqid()]);

        $actual = @$file->filemtime();

        assertThat($actual, equals(-1));
    }

    public function testFileatimeWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['stat' => $message]);

        expect(static function () use ($file): void {
            $file->fileatime();
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testFileatimeWithErrorMessageReturnsNegativeOne(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['stat' => uniqid()]);

        $actual = @$file->fileatime();

        assertThat($actual, equals(-1));
    }

    public function testFilectimeWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['stat' => $message]);

        expect(static function () use ($file): void {
            $file->filectime();
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testFilectimeWithErrorMessageReturnsNegativeOne(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['stat' => uniqid()]);

        $actual = @$file->filectime();

        assertThat($actual, equals(-1));
    }
}
