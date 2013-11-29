<?php

namespace Knp\Raw\Mongo\Transformer;

use Knp\Raw\Transformer as Base;
use Knp\Raw\Transformer;
use DateTime;
use MongoId;
use MongoDate;

class ToObject implements Transformer
{
    public function __construct(Base\ToObject $toObject = null)
    {
        $this->toObject = $toObject ?: new Base\ToObject;
    }

    public function transform($object)
    {
        if ($object instanceof MongoId) {
            return (string) $object;
        }

        if ($object instanceof MongoDate) {
            return $this->toDateTime($object);
        }

        return $this->toObject->transform($object);
    }

    private function toDateTime(MongoDate $date)
    {
        $dateTime = new DateTime;
        $dateTime->setTimestamp($date->sec);

        return $dateTime;
    }
}
