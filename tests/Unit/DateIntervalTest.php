<?php

declare(strict_types=1);

namespace Example\DateTime\Tests\Unit;

use Example\DateTime\DateInterval;
use DateMalformedIntervalStringException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateInterval::class)]
class DateIntervalTest extends TestCase
{
    public static function dataInstanceCreation(): array
    {
        return [
            ['PT4H', 0, 0, 0, 4, 0, 0, 0.0, false],
            ['-PT4H', 0, 0, 0, 4, 0, 0, 0.0, true],
            ['PT-4H', 0, 0, 0, -4, 0, 0, 0.0, false],
            ['PT0,123456S', 0, 0, 0, 0, 0, 0, 0.123456, false],
            ['PT0.123456S', 0, 0, 0, 0, 0, 0, 0.123456, false],
            ['PT0.1234564S', 0, 0, 0, 0, 0, 0, 0.123456, false],
            ['PT0.1234565S', 0, 0, 0, 0, 0, 0, 0.123457, false],
            ['P1Y2M3DT4H5M6.123456S', 1, 2, 3, 4, 5, 6, 0.123456, false],
            ['P-1Y-2M-3DT-4H-5M-6.123456S', -1, -2, -3, -4, -5, -6, -0.123456, false],
        ];
    }

    /**
     * Tests the creation of date interval instances with ISO 8601 interval
     * strings.
     */
    #[DataProvider('dataInstanceCreation')]
    public function testInstanceCreation(
        string $duration,
        int $y,
        int $m,
        int $d,
        int $h,
        int $i,
        int $s,
        float $f,
        bool $invert,
    ): void {
        $interval = new DateInterval($duration);

        self::assertSame($y, $interval->y);
        self::assertSame($m, $interval->m);
        self::assertSame($d, $interval->d);
        self::assertSame($h, $interval->h);
        self::assertSame($i, $interval->i);
        self::assertSame($s, $interval->s);
        self::assertSame($f, $interval->f);
        self::assertSame((int) $invert, $interval->invert);
    }

    /**
     * Tests the creation of a date interval instance with a "precedence
     * representation" interval string (not supported but accepted by default).
     */
    public function testInstanceCreationWithPrecedenceRepresentation(): void
    {
        $this->expectException(DateMalformedIntervalStringException::class);

        new DateInterval('P3DP1MP1Y');
    }

    /**
     * Tests if no special variant of date intervals is created when using
     * createFromDateString().
     */
    public function testCreateFromDateString(): void
    {
        $interval = DateInterval::createFromDateString('+4 hours +1 years +123456 microseconds');

        self::assertSame(1, $interval->y);
        self::assertSame(4, $interval->h);
        self::assertSame(0.123456, $interval->f);
        self::assertEmpty($interval->days ?? null);
        self::assertEmpty($interval->from_string ?? null);
    }
}
