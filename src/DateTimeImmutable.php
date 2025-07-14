<?php

declare(strict_types=1);

namespace Example\DateTime;

use DateInterval;
use DateTimeImmutable as BaseDateTimeImmutable;

class DateTimeImmutable extends BaseDateTimeImmutable
{
    use DateTimeTrait;

    /**
     * @inheritDoc
     */
    public function add(DateInterval | DateDurationInterface $interval): BaseDateTimeImmutable
    {
        if ($interval instanceof DateInterval) {
            return $this->addInterval($interval);
        } else {
            return $this->addDuration($interval);
        }
    }

    /**
     * @inheritDoc
     */
    public function sub(DateInterval | DateDurationInterface $interval): BaseDateTimeImmutable
    {
        if ($interval instanceof DateInterval) {
            return $this->subInterval($interval);
        } else {
            return $this->subDuration($interval);
        }
    }
}
