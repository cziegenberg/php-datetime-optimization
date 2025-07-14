<?php

declare(strict_types=1);

namespace Example\DateTime;

interface DateDurationInterface
{
    /**
     * Adds a duration to the current one and returns a new instance with the
     * result.
     *
     * @param DateDurationInterface|float|int|string $time Seconds and microseconds as float, seconds as int, seconds
     *                                                     and microseconds as string (for example "-123.456789"), or a
     *                                                     duration instance
     *
     * @throws DateInvalidDurationValueException
     */
    public function add(DateDurationInterface | float | int | string $time): DateDurationInterface;

    /**
     * Returns the seconds and microseconds as string (for example -123.456789).
     */
    public function getTime(bool $absolute = false): string;

    /**
     * Returns is the time is inverted (negative).
     */
    public function isInverted(): bool;

    /**
     * Subtracts a duration from the current one and returns a new instance with
     * the result.
     *
     * @param DateDurationInterface|float|int|string $time Seconds and microseconds as float, seconds as int, seconds
     *                                                     and microseconds as string (for example "-123.456789"), or a
     *                                                     duration instance
     *
     * @throws DateInvalidDurationValueException
     */
    public function sub(DateDurationInterface | float | int | string $time): DateDurationInterface;

    /**
     * Returns a new duration instance with the given time.
     *
     * @throws DateInvalidDurationValueException
     */
    public function withTime(float | int | string $time): static;

    /**
     * Returns the string representation of the duration
     * (equals the result of getTime()).
     */
    public function __toString(): string;
}
