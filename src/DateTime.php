<?php

declare(strict_types=1);

namespace Example\DateTime;

use DateInterval;
use DateTime as BaseDateTime;

class DateTime extends BaseDateTime
{
    use DateTimeTrait;

    /**
     * @inheritDoc
     */
    public function add(DateInterval | DateDurationInterface $interval): BaseDateTime
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
    public function sub(DateInterval | DateDurationInterface $interval): BaseDateTime
    {
        if ($interval instanceof DateInterval) {
            return $this->subInterval($interval);
        } else {
            return $this->subDuration($interval);
        }
    }
}
