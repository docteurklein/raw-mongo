<?php

namespace Knp\Raw\Mongo\Transformer;

use Knp\Raw\Transformer as Base;
use Knp\Raw\Transformer;
use DateTime;
use MongoId;
use MongoDate;

class ToArray implements Transformer
{
    public function __construct(Base\ToArray $toArray = null)
    {
        $this->toArray = $toArray ?: new Base\ToArray;
    }

    public function transform($object)
    {
        if (isset($object->_id) && !$object->_id instanceof MongoId) {
            $object->_id = new MongoId($object->_id);
        }

        if ($object instanceof DateTime) {
            return $this->toMongoDate($object);
        }

        return $this->toArray->transform($object);
    }

    private function toMongoDate(DateTime $date)
    {
        return new MongoDate($date->format('U.u'));
    }
}
