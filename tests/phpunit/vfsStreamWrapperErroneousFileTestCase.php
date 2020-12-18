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

use function bovigo\assert\assertEmptyString;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function fclose;
use function feof;
use function fileatime;
use function filectime;
use function filemtime;
use function flock;
use function fopen;
use function fread;
use function fseek;
use function fstat;
use function ftell;
use function ftruncate;
use function fwrite;
use function rand;
use function time;
use function uniqid;

use const E_USER_WARNING;
use const E_WARNING;
use const LOCK_SH;
use const SEEK_SET;

/**
 * Test for bovigo\vfs\vfsStreamWrapper.
 */
class vfsStreamWrapperErroneousFileTestCase extends vfsStreamWrapperBaseTestCase
{
    /**
     * @dataProvider sampleModes
     */
    public function testOpenWithErrorMessageTriggersError(string $mode): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile(uniqid(), ['open' => $message])->at($this->root);

        expect(static function () use ($file, $mode): void {
            fopen($file->url(), $mode);
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    /**
     * @return array<string, string[]>
     */
    public function sampleModes(): array
    {
        return [
            'read' => ['r'],
            'read+write' => ['r+'],
            'write' => ['w'],
            'write+read' => ['w+'],
            'append' => ['a'],
            'append+read' => ['a+'],
            'create' => ['c'],
            'create+read' => ['c+'],
        ];
    }

    public function testReadWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['read' => $message])->at($this->root);

        expect(static function () use ($file): void {
            $fh = fopen($file->url(), 'r');
            fread($fh, rand(1, 10000));
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testReadWithErrorMessageReturnsEmptyString(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['read' => uniqid()])->at($this->root);

        $fh = fopen($file->url(), 'r');
        $actual = @fread($fh, rand(1, 10000));

        assertEmptyString($actual);
    }

    public function testWriteWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['write' => $message])->at($this->root);

        expect(static function () use ($file): void {
            $fh = fopen($file->url(), 'a');
            fwrite($fh, uniqid());
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testWriteWithErrorMessageReturnsZero(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['write' => uniqid()])->at($this->root);

        $fh = fopen($file->url(), 'a');
        $actual = @fwrite($fh, uniqid());

        assertThat($actual, equals(0));
    }

    public function testTruncateWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['truncate' => $message])->at($this->root);

        expect(static function () use ($file): void {
            $fh = fopen($file->url(), 'w+');
            ftruncate($fh, rand(1, 10000));
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testTruncateWithErrorMessageReturnsFalse(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['truncate' => uniqid()])->at($this->root);

        $fh = fopen($file->url(), 'w+');
        $actual = @ftruncate($fh, rand(1, 10000));

        assertFalse($actual);
    }

    public function testEofWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['eof' => $message])->at($this->root);

        expect(static function () use ($file): void {
            $fh = fopen($file->url(), 'w+');
            feof($fh);
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testEofWithErrorMessageReturnsTrue(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['eof' => uniqid()])->at($this->root)->setContent(uniqid());

        $fh = fopen($file->url(), 'w+');
        $actual = @feof($fh);

        assertTrue($actual);
    }

    public function testTellWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['tell' => $message])->at($this->root);

        expect(static function () use ($file): void {
            $fh = fopen($file->url(), 'w');
            fseek($fh, 0, SEEK_SET);
            ftell($fh);
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testTellWithErrorMessageReturnsZero(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['tell' => uniqid()])->at($this->root);

        $fh = fopen($file->url(), 'w');
        @fseek($fh, 1, SEEK_SET);

        $actual = @ftell($fh);

        assertThat($actual, equals(0));
    }

    public function testSeekWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['seek' => $message])->at($this->root);

        expect(static function () use ($file): void {
            $fh = fopen($file->url(), 'w');
            fseek($fh, 0, SEEK_SET);
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testSeekWithErrorMessageReturnsNegativeOne(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['seek' => uniqid()])->at($this->root);

        $fh = fopen($file->url(), 'w');
        $actual = @fseek($fh, 1, SEEK_SET);

        assertThat($actual, equals(-1));
    }

    public function testStatWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['stat' => $message])->at($this->root);

        expect(static function () use ($file): void {
            $fh = fopen($file->url(), 'w');
            fstat($fh);
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testStatWithErrorMessageReturnsFalse(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['stat' => uniqid()])->at($this->root);

        $fh = fopen($file->url(), 'w');
        $actual = @fstat($fh);

        assertFalse($actual);
    }

    public function testLockWithErrorMessageTriggersError(): void
    {
        $message = uniqid();
        $file = vfsStream::newErroneousFile('foo', ['lock' => $message])->at($this->root);

        expect(static function () use ($file): void {
            $fh = fopen($file->url(), 'r');
            flock($fh, LOCK_SH);
        })->triggers(E_USER_WARNING)->withMessage($message);
    }

    public function testLockWithErrorMessageReturnsFalse(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['lock' => uniqid()])->at($this->root);

        $fh = @fopen($file->url(), 'r');
        $actual = @flock($fh, LOCK_SH);
        @fclose($fh); // Close calls lock to unlock

        assertFalse($actual);
    }

    public function testFilemtimeWithErrorMessageTriggersError(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['stat' => uniqid()])->at($this->root);

        expect(static function () use ($file): void {
            filemtime($file->url());
        })->triggers(E_WARNING)->withMessage('filemtime(): stat failed for ' . $file->url());
    }

    public function testFilemtimeWithErrorMessageReturnsFalse(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['stat' => uniqid()])->at($this->root);

        $actual = @filemtime($file->url());

        assertFalse($actual);
    }

    public function testFilemtimeWithoutError(): void
    {
        $file = vfsStream::newErroneousFile('foo', [])->at($this->root);

        $actual = filemtime($file->url());

        assertThat($actual, equals(time(), 1));
    }

    public function testFileatimeWithErrorMessageTriggersError(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['stat' => uniqid()])->at($this->root);

        expect(static function () use ($file): void {
            fileatime($file->url());
        })->triggers(E_WARNING)->withMessage('fileatime(): stat failed for ' . $file->url());
    }

    public function testFileatimeWithErrorMessageReturnsFalse(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['stat' => uniqid()])->at($this->root);

        $actual = @fileatime($file->url());

        assertFalse($actual);
    }

    public function testFileatimeWithoutError(): void
    {
        $file = vfsStream::newErroneousFile('foo', [])->at($this->root);

        $actual = fileatime($file->url());

        assertThat($actual, equals(time(), 1));
    }

    public function testFilectimeWithErrorMessageTriggersError(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['stat' => uniqid()])->at($this->root);

        expect(static function () use ($file): void {
            filectime($file->url());
        })->triggers(E_WARNING)->withMessage('filectime(): stat failed for ' . $file->url());
    }

    public function testFilectimeWithErrorMessageReturnsFalse(): void
    {
        $file = vfsStream::newErroneousFile('foo', ['stat' => uniqid()])->at($this->root);

        $actual = @filectime($file->url());

        assertFalse($actual);
    }

    public function testFilectimeWithoutError(): void
    {
        $file = vfsStream::newErroneousFile('foo', [])->at($this->root);

        $actual = filectime($file->url());

        assertThat($actual, equals(time(), 1));
    }
}
