<?php

declare(strict_types=1);

namespace Example\DateTime\Tests\Unit;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

use function sprintf;

class PhpBugTest extends TestCase
{
    public static function dataIso8601DurationString(): array
    {
        return [
            ['-PT4H'],
            ['PT-4H'],
            ['PT0,123456S'],
            ['PT0.123456S'],
            ['PT0.1234564S'],
            ['PT0.1234565S'],
            ['P1Y2M3DT4H5M6.123456S'],
            ['P-1Y-2M-3DT-4H-5M-6.123456S'],
        ];
    }

    /**
     * This test checks ISO 8601 support.
     */
    #[DataProvider('dataIso8601DurationString')]
    public function testIso8601DurationString(string $duration): void
    {
        $exceptionOccurred = false;

        try {
            new DateInterval($duration);
        } catch (Throwable) {
            $exceptionOccurred = true;
        }

        self::assertFalse(
            $exceptionOccurred,
            sprintf('PHP optimization: Supported ISO duration string "%s" is not accepted.', $duration),
        );
    }

    /**
     * This test checks if unsupported durations strings trigger an exception.
     */
    public function testUnsupportedPrecedenceRepresentation(): void
    {
        $exceptionOccurred = false;

        try {
            new DateInterval('P3DP2MP1Y');
        } catch (Throwable) {
            $exceptionOccurred = true;
        }

        self::assertTrue(
            $exceptionOccurred,
            sprintf('PHP bug: Unsupported ISO duration string "%s" does not trigger an exception.', 'P3DP2MP1Y'),
        );
    }

    /**
     * Checks if the interval used for the creation of the date period is
     * not changed internally.
     *
     * @note
     *     Date period internally modifies the interval. This introduces a
     *     fourth variant of interval found (which seems to be of the
     *     internal type "civil").
     */
    public function testInternalIntervalModification(): void
    {
        $interval = new DateInterval('PT4H');
        $start    = new DateTimeImmutable('2025-01-01 00:00:00', new DateTimeZone('UTC'));
        $period   = new DatePeriod($start, $interval, 3, DatePeriod::EXCLUDE_START_DATE);

        self::assertSame($interval, $period->getDateInterval(), 'PHP bug: Interval changed by DatePeriod.');
    }

    /**
     * This test checks if the period behaves the same as the interval.
     */
    public function testIntervalDuringDstSwitch(): void
    {
        $interval = new DateInterval('PT4H');
        $start    = new DateTimeImmutable('2025-03-30 00:00:00', new DateTimeZone('Europe/Amsterdam'));
        $period   = new DatePeriod($start, $interval, 3, DatePeriod::EXCLUDE_START_DATE);

        self::assertSame(
            $start->add($interval)->format('Y-m-d H:i:s.u'),
            $period->getIterator()->current()->format('Y-m-d H:i:s.u'),
            'PHP bug: DatePeriod interval behavior vs. DateTimeImmutable::add().'
        );
    }

    public function testCreateFromISO8601StringUnsupportedPrecedenceRepresentation(): void
    {
        $exceptionOccurred = false;

        try {
            DatePeriod::createFromISO8601String('R5/20250130T120000Z/P1DP1MP1Y');
        } catch (Throwable) {
            $exceptionOccurred = true;
        }

        self::assertTrue(
            $exceptionOccurred,
            sprintf(
                'PHP bug: Unsupported ISO recurrence string "%s" does not trigger an exception.',
                'R5/20250130T120000Z/P1DP1MP1Y',
            ),
        );
    }
}