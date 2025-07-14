<?php

declare(strict_types=1);

namespace Example\DateTime\Tests\Unit;

use DateInterval as BaseDateInterval;
use DateMalformedIntervalStringException;
use DatePeriod as BaseDatePeriod;
use DateTimeImmutable as BaseDateTimeImmutable;
use DateTimeZone;
use Example\DateTime\DatePeriod;
use Example\DateTime\DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DatePeriod::class)]
class DatePeriodTest extends TestCase
{
    /**
     * Checks if the interval used for the creation of the date period is
     * not changed internally.
     */
    public function testInternalIntervalModification(): void
    {
        $interval = new BaseDateInterval('PT4H');
        $start    = new BaseDateTimeImmutable('2025-01-01 00:00:00', new DateTimeZone('UTC'));
        $period   = new DatePeriod($start, $interval, 3, BaseDatePeriod::EXCLUDE_START_DATE);

        self::assertSame($interval, $period->getDateInterval());
    }

    /**
     * This test checks if the period behaves the same as the interval.
     */
    public function testIntervalDuringDstSwitch(): void
    {
        $interval = new BaseDateInterval('PT4H');
        $start    = new DateTimeImmutable('2025-03-30 00:00:00', new DateTimeZone('Europe/Amsterdam'));
        $period   = new DatePeriod($start, $interval, 3, BaseDatePeriod::EXCLUDE_START_DATE);

        self::assertSame(
            $start->add($interval)->format('Y-m-d H:i:s.u'),
            $period->getIterator()->current()->format('Y-m-d H:i:s.u'),
        );
    }

    /**
     * Tests is the expected exception is thrown.
     */
    public function testUnsupportedPrecedenceRepresentation(): void
    {
        $this->expectException(DateMalformedIntervalStringException::class);

        DatePeriod::createFromISO8601String('R5/20250130T120000Z/P1DP1MP1Y');
    }

    /**
     * Tests is the expected exception is thrown.
     */
    public function testCreateFromISO8601String(): void
    {
        $period   = DatePeriod::createFromISO8601String('R5/20250130T120000Z/-PT4H-5M');
        $interval = $period->getDateInterval();

        self::assertSame(1, $interval->invert);
        self::assertSame(4, $interval->h);
        self::assertSame(-5, $interval->i);
    }
}