<?php

declare(strict_types=1);

namespace Example\DateTime;

use function bcadd;
use function bcsub;
use function explode;
use function is_float;
use function is_int;
use function preg_match;
use function rtrim;
use function sprintf;
use function str_contains;
use function strlen;
use function substr;

class DateDuration implements DateDurationInterface
{
    private string $time;
    private bool $invert;

    /**
     * @param float|int|string $time Seconds and microseconds as float, seconds as int, or seconds and microseconds as
     *                               string (for example "-123.456789")
     *
     * @throws DateInvalidDurationValueException
     */
    public function __construct(float | int | string $time)
    {
        $this->setTime($time);
    }

    /**
     * @inheritDoc
     */
    public function getTime(bool $absolute = false): string
    {
        return (!$absolute && $this->invert ? '-' : '') . $this->time;
    }

    /**
     * @inheritDoc
     */
    public function add(DateDurationInterface | float | int | string $time): DateDurationInterface
    {
        if (!$time instanceof DateDurationInterface) {
            $time = new self($time);
        }

        return $this->withTime(self::addTime($this->getTime(), $time->getTime()));
    }

    /**
     * @inheritDoc
     */
    public function isInverted(): bool
    {
        return $this->invert;
    }

    /**
     * @inheritDoc
     */
    public function sub(DateDurationInterface | float | int | string $time): DateDurationInterface
    {
        if (!$time instanceof DateDurationInterface) {
            $time = new self($time);
        }

        return $this->withTime(self::subTime($this->getTime(), $time->getTime()));
    }

    /**
     * @inheritDoc
     */
    public function withTime(float | int | string $time): static
    {
        $instance = clone $this;
        $instance->setTime($time);

        return $instance;
    }

    /**
     * Adds a time string to another time string.
     */
    private static function addTime(string $timeA, string $timeB): string
    {
        return bcadd($timeA, $timeB, 6);
    }

    /**
     * Sets the internal time value.
     *
     * @throws DateInvalidDurationValueException
     */
    private function setTime(float | int | string $time): void
    {
        if (is_float($time)) {
            $time = (string) $time;
        } elseif (is_int($time)) {
            $time = $time . '.0';
        }

        if (!preg_match('/^-?(?:\d+(?:\.\d*)?|\.\d+)$/', $time)) {
            throw new DateInvalidDurationValueException(
                sprintf('Invalid value for argument "%s".', 'time'),
                1751530857,
            );
        }

        $this->invert = $time[0] === '-';
        $time           = ltrim($time, '-');

        if (str_contains($time, '.')) {
            [$seconds, $microseconds] = explode('.', $time);

            // Round unsupported precision
            $addMicrosecond = false;

            if (strlen($microseconds) > 6) {
                $addMicrosecond = (int) $microseconds[6] >= 5;
                $microseconds   = substr($microseconds, 0, 6);
            }

            $seconds      = (int) $seconds;
            $microseconds = (int) $microseconds;

            if ($addMicrosecond) {
                if ($microseconds < 999999) {
                    $microseconds += 1;
                } else {
                    $seconds     += 1;
                    $microseconds = 0;
                }
            }
        } else {
            $seconds      = (int) $time;
            $microseconds = 0;
        }

        $this->time = $seconds . rtrim('.' . $microseconds, '0.');
    }

    /**
     * Subtracts a time string (seconds argument) from another time string
     * (first argument).
     */
    private static function subTime(string $timeA, string $timeB): string
    {
        return bcsub($timeA, $timeB, 6);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->getTime();
    }
}
