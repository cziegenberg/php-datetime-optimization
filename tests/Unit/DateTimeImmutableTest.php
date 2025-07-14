<?php

declare(strict_types=1);

namespace Example\DateTime\Tests\Unit;

use Example\DateTime\DateDuration;
use Example\DateTime\DateDurationInterface;
use Example\DateTime\DateTimeImmutable;
use DateInterval as BaseDateInterval;
use DateTimeZone as BaseDateTimeZone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateTimeImmutable::class)]
class DateTimeImmutableTest extends TestCase
{
    public static function dataAdd(): array
    {
        return [
            [
                '2025-03-30 01:00:00.000000',
                'Europe/Berlin',
                new BaseDateInterval('PT3H'),
                '2025-03-30 04:00:00.000000',
                '2025-03-30 02:00:00.000000',
            ],
            [
                '2025-03-30 01:00:00.000000',
                'Europe/Berlin',
                new DateDuration('10800.123456'),
                '2025-03-30 05:00:00.123456',
                '2025-03-30 03:00:00.123456',
            ],
            [
                '2025-10-26 01:00:00.000000',
                'Europe/Berlin',
                new BaseDateInterval('PT3H'),
                '2025-10-26 04:00:00.000000',
                '2025-10-26 03:00:00.000000',
            ],
            [
                '2025-10-26 01:00:00.000000',
                'Europe/Berlin',
                new DateDuration('10800'),
                '2025-10-26 03:00:00.000000',
                '2025-10-26 02:00:00.000000',
            ],
        ];
    }

    public static function dataSub(): array
    {
        return [
            [
                '2025-03-30 04:00:00.000000',
                'Europe/Berlin',
                new BaseDateInterval('PT3H'),
                '2025-03-30 01:00:00.000000',
                '2025-03-30 00:00:00.000000',
            ],
            [
                '2025-03-30 04:00:00.000000',
                'Europe/Berlin',
                new DateDuration('10800'),
                '2025-03-30 00:00:00.000000',
                '2025-03-29 23:00:00.000000',
            ],
            [
                '2025-03-30 04:00:00.000000',
                'Europe/Berlin',
                new BaseDateInterval('PT2H'),
                '2025-03-30 03:00:00.000000',
                '2025-03-30 01:00:00.000000',
            ],
            [
                '2025-03-30 04:00:00.000000',
                'Europe/Berlin',
                new DateDuration('7200'),
                '2025-03-30 01:00:00.000000',
                '2025-03-30 00:00:00.000000',
            ],
            [
                '2025-10-26 04:00:00.000000',
                'Europe/Berlin',
                new BaseDateInterval('PT3H'),
                '2025-10-26 01:00:00.000000',
                '2025-10-25 23:00:00.000000',
            ],
            [
                '2025-10-26 04:00:00.000000',
                'Europe/Berlin',
                new DateDuration('10800'),
                '2025-10-26 02:00:00.000000',
                '2025-10-26 00:00:00.000000',
            ],
            [
                '2025-10-26 04:00:00.000000',
                'Europe/Berlin',
                new DateDuration('10800'),
                '2025-10-26 02:00:00.000000',
                '2025-10-26 00:00:00.000000',
            ],
        ];
    }

    #[DataProvider('dataAdd')]
    public function testAdd(
        string $datetime,
        string $timezone,
        BaseDateInterval | DateDurationInterface $duration,
        string $expectedLocalDatetime,
        string $expectedUtcDatetime,
    ): void {
        $datetime = new DateTimeImmutable($datetime, new BaseDateTimeZone($timezone));
        $datetime = $datetime->add($duration);

        self::assertSame($expectedLocalDatetime, $datetime->format('Y-m-d H:i:s.u'));
        self::assertSame(
            $expectedUtcDatetime,
            $datetime->setTimezone(new BaseDateTimeZone('UTC'))->format('Y-m-d H:i:s.u'),
        );
    }

    public function testDiffIntervalVsDuration(): void
    {
        $timezone   = new BaseDateTimeZone('Europe/Berlin');
        $datetimeA  = new DateTimeImmutable('2025-03-30 00:00:00', $timezone);
        $datetimeB  = new DateTimeImmutable('2025-03-30 05:00:00', $timezone);

        $intervalA   = $datetimeA->diff($datetimeB);
        $intervalB   = $datetimeB->diff($datetimeA, true);
        $durationA   = $datetimeA->diff($datetimeB, false, true);
        $durationB   = $datetimeB->diff($datetimeA, true, true);

        self::assertSame(5, $intervalA->h);
        self::assertSame(5, $intervalB->h);
        self::assertSame('14400', $durationA->getTime());
        self::assertSame('14400', $durationB->getTime());
    }

    public function testDiffPhpBugCorrectionA(): void
    {
        $timezone   = new BaseDateTimeZone('Europe/Berlin');
        $datetimeA  = new DateTimeImmutable('2025-03-30 01:00:00', $timezone);
        $datetimeB  = new DateTimeImmutable('2025-03-30 04:00:00', $timezone);

        self::assertSame(3, $datetimeA->diff($datetimeB)->h);
        self::assertSame(3, $datetimeB->diff($datetimeA)->h);
    }

    public function testDiffPhpBugCorrectionB(): void
    {
        $datetimeA  = new DateTimeImmutable('2025-03-01 00:00:00', new BaseDateTimeZone('Europe/Berlin'));
        $datetimeB  = new DateTimeImmutable('2025-05-01 00:00:00', new BaseDateTimeZone('Europe/Amsterdam'));
        $diffA      = $datetimeA->diff($datetimeB);
        $diffB      = $datetimeB->diff($datetimeA);

        self::assertSame(2, $diffA->m);
        self::assertSame(2, $diffB->m);
        self::assertSame(0, $diffA->d);
        self::assertSame(0, $diffB->d);
    }

    #[DataProvider('dataSub')]
    public function testSub(
        string $datetime,
        string $timezone,
        BaseDateInterval | DateDurationInterface $duration,
        string $expectedLocalDatetime,
        string $expectedUtcDatetime,
    ): void {
        $datetime = new DateTimeImmutable($datetime, new BaseDateTimeZone($timezone));
        $datetime = $datetime->sub($duration);

        self::assertSame($expectedLocalDatetime, $datetime->format('Y-m-d H:i:s.u'));
        self::assertSame(
            $expectedUtcDatetime,
            $datetime->setTimezone(new BaseDateTimeZone('UTC'))->format('Y-m-d H:i:s.u'),
        );
    }
}
