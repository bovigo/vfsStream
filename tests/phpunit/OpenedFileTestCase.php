<?php

declare(strict_types=1);

/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bovigo\vfs\tests;

use bovigo\callmap\NewInstance;
use bovigo\vfs\content\StringBasedFileContent;
use bovigo\vfs\OpenedFile;
use bovigo\vfs\vfsStreamFile;
use bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isSameAs;
use function bovigo\callmap\verify;
use function rand;
use function strlen;
use function uniqid;

use const SEEK_CUR;
use const SEEK_END;
use const SEEK_SET;

/**
 * Test for bovigo\vfs\OpenedFile.
 */
class OpenedFileTestCase extends TestCase
{
    /** @var OpenedFile */
    private $fixture;

    /** @var vfsStreamFile&ClassProxy */
    private $base;

    /** @var FileContent&ClassProxy */
    private $content;

    protected function setUp(): void
    {
        parent::setUp();

        $this->content = NewInstance::of(StringBasedFileContent::class, ['foobarbaz']);
        $this->base = NewInstance::of(vfsStreamFile::class, [uniqid()]);
        $this->base->withContent($this->content);

        $this->fixture = new OpenedFile($this->base);
    }

    public function testGetBaseFile(): void
    {
        $actual = $this->fixture->getBaseFile();

        assertThat($actual, isSameAs($this->base));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testOpenCallsBase(): void
    {
        $this->fixture->open();

        verify($this->base, 'open')->wasCalledOnce();
        verify($this->base, 'open')->receivedNothing();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testOpenForAppendCallsBase(): void
    {
        $this->fixture->openForAppend();

        verify($this->base, 'openForAppend')->wasCalledOnce();
        verify($this->base, 'openForAppend')->receivedNothing();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testOpenForAppendChecksPosition(): void
    {
        $this->fixture->openForAppend();

        verify($this->content, 'bytesRead')->wasCalledOnce();
        verify($this->content, 'bytesRead')->receivedNothing();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testOpenWithTruncateCallsBase(): void
    {
        $this->fixture->openWithTruncate();

        verify($this->base, 'openWithTruncate')->wasCalledOnce();
        verify($this->base, 'openWithTruncate')->receivedNothing();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testOpenWithTruncateChecksPosition(): void
    {
        $this->fixture->openWithTruncate();

        verify($this->content, 'bytesRead')->wasCalledOnce();
        verify($this->content, 'bytesRead')->receivedNothing();
    }

    public function testReadCallsBase(): void
    {
        $bytes = rand(1, 10);

        $this->fixture->read($bytes);

        verify($this->base, 'read')->wasCalledOnce();
        verify($this->base, 'read')->received($bytes);
    }

    public function testReadRestoresPreviousPosition(): void
    {
        $this->fixture->read(3);
        $this->fixture->read(6);

        verify($this->content, 'seek')->wasCalled(2);
        verify($this->content, 'seek')->receivedOn(1, 0, SEEK_SET);
        verify($this->content, 'seek')->receivedOn(2, 3, SEEK_SET);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testReadChecksPosition(): void
    {
        $this->fixture->read(rand(1, 10));

        verify($this->content, 'bytesRead')->wasCalledOnce();
        verify($this->content, 'bytesRead')->receivedNothing();
    }

    public function testReadResponse(): void
    {
        $data = uniqid();
        $this->base->returns(['read' => $data]);

        $actual = $this->fixture->read(strlen($data));

        assertThat($actual, equals($data));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testReadUntilEndCallsBase(): void
    {
        $this->fixture->readUntilEnd();

        verify($this->base, 'readUntilEnd')->wasCalledOnce();
        verify($this->base, 'readUntilEnd')->receivedNothing();
    }

    public function testReadUntilEndRestoresPreviousPosition(): void
    {
        $this->fixture->read(3);
        $this->fixture->readUntilEnd();

        verify($this->content, 'seek')->wasCalled(2);
        verify($this->content, 'seek')->receivedOn(1, 0, SEEK_SET);
        verify($this->content, 'seek')->receivedOn(2, 3, SEEK_SET);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testReadUntilEndChecksPosition(): void
    {
        $this->fixture->readUntilEnd();

        verify($this->content, 'bytesRead')->wasCalledOnce();
        verify($this->content, 'bytesRead')->receivedNothing();
    }

    public function testReadUntilEndResponse(): void
    {
        $data = uniqid();
        $this->base->returns(['readUntilEnd' => $data]);

        $actual = $this->fixture->readUntilEnd();

        assertThat($actual, equals($data));
    }

    public function testWriteCallsBase(): void
    {
        $data = uniqid();

        $this->fixture->write($data);

        verify($this->base, 'write')->wasCalledOnce();
        verify($this->base, 'write')->received($data);
    }

    public function testWriteRestoresPreviousPosition(): void
    {
        $this->fixture->write('foobar');
        $this->fixture->write(uniqid());

        verify($this->content, 'seek')->wasCalled(2);
        verify($this->content, 'seek')->receivedOn(1, 0, SEEK_SET);
        verify($this->content, 'seek')->receivedOn(2, 6, SEEK_SET);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testWriteChecksPosition(): void
    {
        $this->fixture->write(uniqid());

        verify($this->content, 'bytesRead')->wasCalledOnce();
        verify($this->content, 'bytesRead')->receivedNothing();
    }

    public function testWriteResponse(): void
    {
        $bytes = rand(1, 10);
        $this->base->returns(['write' => $bytes]);

        $actual = $this->fixture->write(uniqid());

        assertThat($actual, equals($bytes));
    }

    public function testTruncateCallsBase(): void
    {
        $bytes = rand(1, 10);

        $this->fixture->truncate($bytes);

        verify($this->base, 'truncate')->wasCalledOnce();
        verify($this->base, 'truncate')->received($bytes);
    }

    public function testTruncateRestoresPreviousPosition(): void
    {
        $this->fixture->read(3);
        $this->fixture->truncate(6);

        verify($this->content, 'seek')->wasCalled(2);
        verify($this->content, 'seek')->receivedOn(1, 0, SEEK_SET);
        verify($this->content, 'seek')->receivedOn(2, 3, SEEK_SET);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testTruncateDoesNotCheckPosition(): void
    {
        $this->fixture->truncate(rand(1, 10));

        // truncate does not move the pointer
        verify($this->content, 'bytesRead')->wasNeverCalled();
    }

    public function testTruncateResponse(): void
    {
        $response = (bool) rand(0, 1);
        $this->base->returns(['truncate' => $response]);

        $actual = $this->fixture->truncate(rand(1, 10));

        assertThat($actual, equals($response));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testEofCallsBase(): void
    {
        $this->fixture->eof();

        verify($this->base, 'eof')->wasCalledOnce();
        verify($this->base, 'eof')->receivedNothing();
    }

    public function testEofRestoresPreviousPosition(): void
    {
        $this->fixture->read(3);
        $this->fixture->eof();

        verify($this->content, 'seek')->wasCalled(2);
        verify($this->content, 'seek')->receivedOn(1, 0, SEEK_SET);
        verify($this->content, 'seek')->receivedOn(2, 3, SEEK_SET);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testEofDoesNotCheckPosition(): void
    {
        $this->fixture->eof();

        // eof does not move the pointer
        verify($this->content, 'bytesRead')->wasNeverCalled();
    }

    public function testEofResponse(): void
    {
        $response = (bool) rand(0, 1);
        $this->base->returns(['eof' => $response]);

        $actual = $this->fixture->eof();

        assertThat($actual, equals($response));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testGetBytesReadCallsBase(): void
    {
        $this->fixture->getBytesRead();

        verify($this->base, 'getBytesRead')->wasCalledOnce();
        verify($this->base, 'getBytesRead')->receivedNothing();
    }

    public function testGetBytesReadRestoresPreviousPosition(): void
    {
        $this->fixture->read(3);
        $this->fixture->getBytesRead();

        verify($this->content, 'seek')->wasCalled(2);
        verify($this->content, 'seek')->receivedOn(1, 0, SEEK_SET);
        verify($this->content, 'seek')->receivedOn(2, 3, SEEK_SET);
    }

    public function testGetBytesReadResponse(): void
    {
        $bytes = rand(1, 10);
        $this->fixture->read($bytes);

        $actual = $this->fixture->getBytesRead();

        assertThat($actual, equals($bytes));
    }

    public function testSeekCallsBase(): void
    {
        $offset = rand(1, 10);
        $whence = rand(1, 10);

        $this->fixture->seek($offset, $whence);

        verify($this->base, 'seek')->wasCalledOnce();
        verify($this->base, 'seek')->received($offset, $whence);
    }

    /**
     * @param int[] $expected
     *
     * @dataProvider sampleSeeks
     */
    public function testSeekCallsContentSeek(int $offset, int $whence, array $expected): void
    {
        $this->base->returns(['seek' => (bool) rand(0, 1)]);

        $this->fixture->seek($offset, $whence);

        verify($this->content, 'seek')->wasCalledOnce();
        verify($this->content, 'seek')->received(...$expected);
    }

    /**
     * @return mixed[]
     */
    public function sampleSeeks(): array
    {
        $offset = rand();

        return [
            'SEEK_CUR' => [
                'offset' => $offset,
                'whence' => SEEK_CUR,
                'expected' => [0, SEEK_SET],
            ],
            'SEEK_END' => [
                'offset' => $offset,
                'whence' => SEEK_END,
                'expected' => [0, SEEK_SET],
            ],
        ];
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSeekDoesNotCallContentSeek(): void
    {
        $this->base->returns(['seek' => (bool) rand(0, 1)]);

        $this->fixture->seek(rand(1, 10), SEEK_SET);

        verify($this->content, 'seek')->wasNeverCalled();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSeekChecksPosition(): void
    {
        $this->fixture->seek(rand(1, 10), SEEK_SET);

        verify($this->content, 'bytesRead')->wasCalledOnce();
        verify($this->content, 'bytesRead')->receivedNothing();
    }

    public function testSeekResponse(): void
    {
        $response = (bool) rand(0, 1);
        $this->base->returns(['seek' => $response]);

        $actual = $this->fixture->seek(rand(1, 10), SEEK_SET);

        assertThat($actual, equals($response));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSizeCallsBase(): void
    {
        $this->fixture->size();

        verify($this->base, 'size')->wasCalledOnce();
        verify($this->base, 'size')->receivedNothing();
    }

    public function testSizeResponse(): void
    {
        $size = rand(1, 10);
        $this->base->returns(['size' => $size]);

        $actual = $this->fixture->size();

        assertThat($actual, equals($size));
    }

    public function testLockCallsBase(): void
    {
        $resource = new vfsStreamWrapper();
        $operation = rand();

        $this->fixture->lock($resource, $operation);

        verify($this->base, 'lock')->wasCalledOnce();
        verify($this->base, 'lock')->received($resource, $operation);
    }

    public function testLockResponse(): void
    {
        $resource = new vfsStreamWrapper();
        $response = (bool) rand(0, 1);
        $this->base->returns(['lock' => $response]);

        $actual = $this->fixture->lock($resource, rand());

        assertThat($actual, equals($response));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testGetTypeCallsBase(): void
    {
        $this->fixture->getType();

        verify($this->base, 'getType')->wasCalledOnce();
        verify($this->base, 'getType')->receivedNothing();
    }

    public function testGetTypeResponse(): void
    {
        $type = rand(1, 10);
        $this->base->returns(['getType' => $type]);

        $actual = $this->fixture->getType();

        assertThat($actual, equals($type));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testFilemtimeCallsBase(): void
    {
        $this->fixture->filemtime();

        verify($this->base, 'filemtime')->wasCalledOnce();
        verify($this->base, 'filemtime')->receivedNothing();
    }

    public function testFilemtimeResponse(): void
    {
        $time = rand(1, 10);
        $this->base->returns(['filemtime' => $time]);

        $actual = $this->fixture->filemtime();

        assertThat($actual, equals($time));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testFileatimeCallsBase(): void
    {
        $this->fixture->fileatime();

        verify($this->base, 'fileatime')->wasCalledOnce();
        verify($this->base, 'fileatime')->receivedNothing();
    }

    public function testFileatimeResponse(): void
    {
        $time = rand(1, 10);
        $this->base->returns(['fileatime' => $time]);

        $actual = $this->fixture->fileatime();

        assertThat($actual, equals($time));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testFilectimeCallsBase(): void
    {
        $this->fixture->filectime();

        verify($this->base, 'filectime')->wasCalledOnce();
        verify($this->base, 'filectime')->receivedNothing();
    }

    public function testFilectimeResponse(): void
    {
        $time = rand(1, 10);
        $this->base->returns(['filectime' => $time]);

        $actual = $this->fixture->filectime();

        assertThat($actual, equals($time));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testGetPermissionsCallsBase(): void
    {
        $this->fixture->getPermissions();

        verify($this->base, 'getPermissions')->wasCalledOnce();
        verify($this->base, 'getPermissions')->receivedNothing();
    }

    public function testGetPermissionsResponse(): void
    {
        $response = rand(1, 10);
        $this->base->returns(['getPermissions' => $response]);

        $actual = $this->fixture->getPermissions();

        assertThat($actual, equals($response));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testGetUserCallsBase(): void
    {
        $this->fixture->getUser();

        verify($this->base, 'getUser')->wasCalledOnce();
        verify($this->base, 'getUser')->receivedNothing();
    }

    public function testGetUserResponse(): void
    {
        $response = rand(1, 10);
        $this->base->returns(['getUser' => $response]);

        $actual = $this->fixture->getUser();

        assertThat($actual, equals($response));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testGetGroupCallsBase(): void
    {
        $this->fixture->getGroup();

        verify($this->base, 'getGroup')->wasCalledOnce();
        verify($this->base, 'getGroup')->receivedNothing();
    }

    public function testGetGroupResponse(): void
    {
        $response = rand(1, 10);
        $this->base->returns(['getGroup' => $response]);

        $actual = $this->fixture->getGroup();

        assertThat($actual, equals($response));
    }

    public function testIsReadableCallsBase(): void
    {
        $user = rand();
        $group = rand();

        $this->fixture->isReadable($user, $group);

        verify($this->base, 'isReadable')->wasCalledOnce();
        verify($this->base, 'isReadable')->received($user, $group);
    }

    public function testIsReadableResponse(): void
    {
        $response = rand(1, 10);
        $this->base->returns(['isReadable' => $response]);

        $actual = $this->fixture->isReadable(rand(), rand());

        assertThat($actual, equals($response));
    }

    public function testIsWritableCallsBase(): void
    {
        $user = rand();
        $group = rand();

        $this->fixture->isWritable($user, $group);

        verify($this->base, 'isWritable')->wasCalledOnce();
        verify($this->base, 'isWritable')->received($user, $group);
    }

    public function testIsWritableResponse(): void
    {
        $response = rand(1, 10);
        $this->base->returns(['isWritable' => $response]);

        $actual = $this->fixture->isWritable(rand(), rand());

        assertThat($actual, equals($response));
    }

    public function testIsExecutableCallsBase(): void
    {
        $user = rand();
        $group = rand();

        $this->fixture->isExecutable($user, $group);

        verify($this->base, 'isExecutable')->wasCalledOnce();
        verify($this->base, 'isExecutable')->received($user, $group);
    }

    public function testIsExecutableResponse(): void
    {
        $response = rand(1, 10);
        $this->base->returns(['isExecutable' => $response]);

        $actual = $this->fixture->isExecutable(rand(), rand());

        assertThat($actual, equals($response));
    }
}
