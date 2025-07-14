<?php
declare(strict_types=1);

namespace Example\DateTime;

use DateInterval as BaseDateInterval;
use DateTimeImmutable as BaseDateTimeImmutable;
use DateTimeInterface as BaseDateTimeInterface;
use DateTimeZone;
use ReturnTypeWillChange;

trait DateTimeTrait
{
    /**
     * @inheritDoc
     *
     * @return ($accurate is true ? DateDurationInterface : BaseDateInterval)
     */
    #[ReturnTypeWillChange]
    public function diff(
        BaseDateTimeInterface $targetObject,
        bool                  $absolute = false,
        bool                  $accurate = false,
    ): BaseDateInterval | DateDurationInterface {
        if ($accurate) {
            $start = new DateDuration($this->format('U.u'));
            $end   = new DateDuration($targetObject->format('U.u'));

            if ($absolute && $this > $targetObject) {
                return $start->sub($end);
            } else {
                return $end->sub($start);
            }
        } else {
            // PHP behavior fix 1:
            // Ensure, that the target datetime is in the same timezone
            $target = BaseDateTimeImmutable::createFromInterface($targetObject);
            $target = $target->setTimezone($this->getTimezone());

            // PHP behavior fix 2:
            // Bypass the wrong behavior off "wall" intervals with DST-switches
            // by doing the diff() in UTC (not by converting times to UTC, but by
            // handling local times like UTC times)
            $timezone = new DateTimeZone('UTC');
            /** @noinspection PhpUnhandledExceptionInspection */
            $source   = new BaseDateTimeImmutable($this->format('Y-m-d H:i:s.u'), $timezone);
            /** @noinspection PhpUnhandledExceptionInspection */
            $target   = new BaseDateTimeImmutable($target->format('Y-m-d H:i:s.u'), $timezone);

            $baseDiff = $source->diff($target, $absolute);

            // Convert the date interval instance to the extended one
            $diff         = new DateInterval('PT0S');
            $diff->y      = $baseDiff->y;
            $diff->m      = $baseDiff->m;
            $diff->d      = $baseDiff->d;
            $diff->h      = $baseDiff->h;
            $diff->i      = $baseDiff->i;
            $diff->s      = $baseDiff->s;
            $diff->f      = $baseDiff->f;
            $diff->invert = $baseDiff->invert;

            return $diff;
        }
    }

    /**
     * @inheritDoc
     */
    private function addDuration(DateDurationInterface $duration): static
    {
        $current  = new DateDuration($this->format('U.u'));
        $time     = $current->add($duration);

        return $this->applyDuration($time);
    }

    /**
     * Does an add() that bypasses the wrong PHP behavior with "wall" intervals
     * in combination with DST-switches.
     */
    private function addInterval(BaseDateInterval $interval): static
    {
        // PHP behavior fix:
        // Do the add() in UTC (not by converting times to UTC, but by
        // handling local times like UTC times)
        /** @noinspection PhpUnhandledExceptionInspection */
        $datetime = new BaseDateTimeImmutable($this->format('Y-m-d H:i:s.u'), new DateTimeZone('UTC'));

        return $this->applyFixedTimeUnits($datetime->add($interval));
    }

    /**
     * @inheritDoc
     */
    private function subDuration(DateDurationInterface $duration): static
    {
        $current  = new DateDuration($this->format('U.u'));
        $time     = $current->sub($duration);

        return $this->applyDuration($time);
    }

    /**
     * Applies the
     */
    private function applyFixedTimeUnits(BaseDateTimeInterface $datetime): static
    {
        // Get the new values for all time units
        [$y, $m, $d, $h, $i, $s, $f] = explode('|', $datetime->format('Y|n|j|G|i|s|u'));

        return $this->setDate((int)$y, (int)$m, (int)$d)->setTime((int)$h, (int)$i, (int)$s)->setMicrosecond((int)$f);
    }

    /**
     * Does a sub() that bypasses the wrong PHP behavior with "wall" intervals
     * in combination with DST-switches.
     */
    private function subInterval(BaseDateInterval $interval): static
    {
        // PHP behavior fix:
        // Do the sub() in UTC (not by converting times to UTC, but by
        // handling local times like UTC times)
        /** @noinspection PhpUnhandledExceptionInspection */
        $datetime = new BaseDateTimeImmutable($this->format('Y-m-d H:i:s.u'), new DateTimeZone('UTC'));

        return $this->applyFixedTimeUnits($datetime->sub($interval));
    }


    /**
     * Sets the current time of the date time instance based on the time in the
     * given duration.
     */
    private function applyDuration(DateDurationInterface $duration): static
    {
        $time = $duration->getTime();

        // Handle date time instances
        if (str_contains($time, '.')) {
            [$seconds, $microseconds] = explode('.', $time);
        } else {
            // Should never happen, because fraction always set
            // @codeCoverageIgnoreStart
            $seconds      = $time;
            $microseconds = 0;
            // @codeCoverageIgnoreEnd
        }

        return $this->setTimestamp((int) $seconds)->setMicrosecond((int) $microseconds);
    }
}
