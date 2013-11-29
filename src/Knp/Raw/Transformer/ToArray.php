<?php

namespace Knp\Raw\Transformer;

use DateTime;
use MongoId;
use MongoDate;

class ToArray
{
    private function transform($object)
    {
        if (is_object($object)) {
            return $this->transformObject($object);
        }

        if (is_array($object)) {
            return $this->transformArray($object);
        }

        return $object;
    }

    private function transformObject($object)
    {
        $refl = new \ReflectionObject($object);
        foreach ($refl->getProperties() as $property) {

            $property->setAccessible(true);
            $value = $property->getValue($object);

            $property->setValue($object, $this->transform($value));
        }

        return $object;
    }

    private function transformArray(array $object)
    {
        foreach ($object as $key => $value) {
            $object[$key] = $this->transform($value);
        }

        return $object;
    }
}
