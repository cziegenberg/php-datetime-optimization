<?php

declare(strict_types=1);

namespace Example\DateTime;

use DateInterval as BaseDateInterval;
use DateMalformedIntervalStringException as BaseDateMalformedIntervalStringException;
use DateTimeImmutable as BaseDateTimeImmutable;
use DateTimeZone as BaseDateTimeZone;

use function abs;
use function explode;
use function preg_match;
use function round;
use function sprintf;
use function str_replace;

class DateInterval extends BaseDateInterval
{
    private const string PATTERN_PRECEDENCE_FORMAT    = '/(P[^P]+){2,}/';
    private const string PATTERN_ALLOWED_FORMAT_BASIC = '/^-?P(.+[YMWD]|T.+[HMS]|.+[YMWD]T.+[HMS])$/';
    private const string PATTERN_ALLOWED_FORMAT_UNITS = '/^(-)?P(?:(-?\d+)Y)?(?:(-?\d+)M)?(?:(-?\d+)W)?(?:(-?\d+)D)?' .
                                                        '(?:T(?:(-?\d+)H)?(?:(-?\d+)M)?(?:(-?\d+(?:[.,]\d+)?)S)?)?$/';

    public function __construct(string $duration)
    {
        // PHP behavior fix:
        // Do not accept "precedence representation" format, because this must
        // be handled differently than done by PHP (with truncation and in the
        // given order), so the current results do not comply with ISO 8601.
        if (preg_match(self::PATTERN_PRECEDENCE_FORMAT, $duration)) {
            throw new BaseDateMalformedIntervalStringException(
                sprintf('Unsupported precedence representation format (%s)', $duration),
                1751308845,
            );
        }

        if (preg_match(self::PATTERN_ALLOWED_FORMAT_BASIC, $duration)) {
            if (preg_match(self::PATTERN_ALLOWED_FORMAT_UNITS, $duration, $matches)) {
                $invert      = (int) (($matches[1] ?? '') === '-');
                $year        = (int) ($matches[2] ?? 0);
                $month       = (int) ($matches[3] ?? 0);
                $day         = (int) ($matches[5] ?? 0) + ((int) ($matches[4] ?? 0) * 7);
                $hour        = (int) ($matches[6] ?? 0);
                $minute      = (int) ($matches[7] ?? 0);

                $secondParts = explode('.', str_replace(',', '.', ($matches[8] ?? '0')));
                $second      = (int) $secondParts[0];
                $microsecond = (int) ($secondParts[1] ?? 0) * ($second < 0 ? -1 : 1);
                $fraction    = (float) ($microsecond < 0 ? '-0.' . abs($microsecond) : '0.' . $microsecond);
                $fraction    = round($fraction, 6);

                parent::__construct('PT0S');

                $this->y      = $year;
                $this->m      = $month;
                $this->d      = $day;
                $this->h      = $hour;
                $this->i      = $minute;
                $this->s      = $second;
                $this->f      = $fraction;
                $this->invert = $invert;
            }
        }
    }

    /**
     * @inheritDoc
     *
     * @return DateInterval
     *
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @todo The processing order of time scale components should be documented
     *       in PHP!
     */
    public static function createFromDateString(string $datetime): BaseDateInterval
    {
        // @todo:
        //     Instead of this, the given string SHOULD be parsed here to
        //     extract the contained time scale components and not merge them
        //     when calling modify(). This could result in wrong results
        //     (because of leap years), but it's enough for this example...
        /** @noinspection PhpUnhandledExceptionInspection */
        $baseDateTime     = new BaseDateTimeImmutable('now', new BaseDateTimeZone('UTC'));
        /** @noinspection PhpUnhandledExceptionInspection */
        $modifiedDateTime = $baseDateTime->modify($datetime);
        $diff             = $baseDateTime->diff($modifiedDateTime);

        $instance          = new self('PT0S');
        $instance->y       = $diff->y;
        $instance->m       = $diff->m;
        $instance->d       = $diff->d;
        $instance->h       = $diff->h;
        $instance->i       = $diff->i;
        $instance->s       = $diff->s;
        $instance->f       = $diff->f;
        $instance->invert  = $diff->invert;

        return $instance;
    }
}
