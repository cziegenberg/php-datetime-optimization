<?php

declare(strict_types=1);

namespace Example\DateTime\Tests\Unit;

use Example\DateTime\DateDurationInterface;
use Example\DateTime\DateInvalidDurationValueException;
use Example\DateTime\DateDuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateDuration::class)]
class DateDurationTest extends TestCase
{
    public static function dataAdd(): array
    {
        return [
            [123.1, '-123.1', '0'],
            [128, 127.9999999, '256'],
            ['-178', -22, '-200'],
            [-123, new DateDuration(133.0), '10'],
        ];
    }

    public static function dataSub(): array
    {
        return [
            [123.1, '123.1', '0'],
            [128, -127.9999999, '256'],
            ['-178', 22, '-200'],
            [-123, new DateDuration(-133.0), '10'],
        ];
    }

    public static function dataInstanceCreation(): array
    {
        return [
            [123.456789, '123.456789', false],
            [-123.0, '-123', true],
            [456, '456', false],
            ['-123', '-123', true],
            ['123.123456789', '123.123457', false],
            ['-123.987654321', '-123.987654', true],
            ['123.9999999', '124', false],
            ['-.12345', '-0.12345', true],
            ['12800', '12800', false],
        ];
    }

    /**
     * Tests different duration values used to create a new instance.
     */
    #[DataProvider('dataInstanceCreation')]
    public function testInstanceCreation(float|int|string $time, string $expectedTime, bool $isInverted): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $duration = new DateDuration($time);

        self::assertSame($expectedTime, $duration->getTime());
        self::assertSame($isInverted, $duration->isInverted());
        self::assertSame($expectedTime, (string) $duration);
    }

    /**
     * Tests an invalid duration value used to create a new instance.
     */
    public function testInstanceCreationInvalidDurationValue(): void
    {
        $this->expectException(DateInvalidDurationValueException::class);

        new DateDuration('1,123456');
    }

    /**
     * Tests the add() method
     */
    #[DataProvider('dataAdd')]
    public function testAdd(
        float|int|string $time,
        DateDurationInterface|float|int|string $timeToAdd,
        string $expectedTime,
    ): void {
        /** @noinspection PhpUnhandledExceptionInspection */
        $duration = new DateDuration($time);
        /** @noinspection PhpUnhandledExceptionInspection */
        $duration = $duration->add($timeToAdd);

        self::assertSame($expectedTime, $duration->getTime());
    }

    /**
     * Tests the sub() method
     */
    #[DataProvider('dataSub')]
    public function testSub(
        float|int|string $time,
        DateDurationInterface|float|int|string $timeToAdd,
        string $expectedTime,
    ): void {
        /** @noinspection PhpUnhandledExceptionInspection */
        $duration = new DateDuration($time);
        /** @noinspection PhpUnhandledExceptionInspection */
        $duration = $duration->sub($timeToAdd);

        self::assertSame($expectedTime, $duration->getTime());
    }

    /**
     * Tests the withTime() method
     */
    #[DataProvider('dataInstanceCreation')]
    public function testWithTime(float|int|string $time, string $expectedTime, bool $isInverted): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $duration = new DateDuration(0);
        /** @noinspection PhpUnhandledExceptionInspection */
        $duration = $duration->withTime($time);

        self::assertSame($expectedTime, $duration->getTime());
        self::assertSame($isInverted, $duration->isInverted());
        self::assertSame($expectedTime, (string) $duration);
    }
}
