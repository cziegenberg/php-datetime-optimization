<?php

declare(strict_types=1);

namespace Example\DateTime;

use DateInterval as BaseDateInterval;
use DatePeriod as BaseDatePeriod;
use DateTimeInterface;

use function ltrim;

/**
 * @inheritDoc
 */
class DatePeriod extends BaseDatePeriod
{
    private BaseDateInterval $realInterval;

    /**
     * @note
     *     The old constructor variant to create an instance from an ISO string
     *     is not implemented here, because the static method
     *     "createFromISO8601String" should be used instead.
     */
    public function __construct(
        DateTimeInterface       $start,
        BaseDateInterval        $interval,
        DateTimeInterface | int $endOrRecurrence,
        int                     $options = 0,
    ) {
        // @note
        //    Calling the original constructor destroys converts the interval
        //    back to a standard one (without accurate time) so set the real
        //    one to use as a workaround...
        $this->realInterval = $interval;

        parent::__construct($start, $interval, $endOrRecurrence, $options);
    }

    /**
     * @inheritDoc
     */
    public static function createFromISO8601String(string $specification, int $options = 0): static
    {
        [$recurrences, $datetime, $interval] = explode('/', $specification);

        // TODO: Add argument checks

        $recurrences = (int) ltrim($recurrences, 'R');
        $interval    = new DateInterval($interval);
        $datetime    = new DateTimeImmutable($datetime);

        return new self($datetime, $interval, $recurrences);
    }

    /**
     * @inheritDoc
     */
    public function getDateInterval(): BaseDateInterval
    {
         return $this->realInterval;
    }
}
