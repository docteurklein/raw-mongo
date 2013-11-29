<?php

namespace Knp\Raw\Mongo;

use MongoId;

class Transformer
{
    function __construct(Transformer\ToObject $toObject = null, Transformer\ToArray $toArray = null)
    {
        $this->toObject = $toObject ?: new Transformer\ToObject;
        $this->toArray = $toArray ?: new Transformer\ToArray;
    }

    public function toObject($object)
    {
        return $this->toObject->transform($object);
    }

    public function toArray($object)
    {
        return $this->toArray->transform($object);
    }
}
