<?php

namespace Knp\Raw\Mongo;

use \MongoCollection;
use \MongoCursor;
use \MongoId;
use \MongoDate;
use \DateTime;
use \ArrayIterator;

class Repository
{
    private $collection;
    private $factory;

    public function __construct(MongoCollection $collection, callable $factory)
    {
        $this->collection = $collection;
        $this->factory = $factory;
    }

    public function transform($object)
    {
        $destination = $this->newInstance();
        $this->copy((object) $object, $destination);

        return $this->doTransform($destination);
    }

    public function find($id)
    {
        $object = $this->collection->findOne(['_id' => $this->toMongoId($id)]);
        $object = $this->transform($object);

        return $object;
    }

    public function findAll()
    {
        return $this->hydrateCursor($this->collection->find());
    }

    public function save($object)
    {
        $object = $this->reverseTransform($object);

        return $this->collection->save($object);
    }

    private function hydrateCursor(MongoCursor $cursor)
    {
        $results = [];
        foreach ($cursor as $document) {
            $results[] = $this->transform($document);
        }

        return $this->toArrayIterator($results);
    }

    private function copy($source, $destination)
    {
        $refl = new \ReflectionObject($source);

        foreach ($refl->getProperties() as $property) {
            $property->setAccessible(true);
            $property->setValue($destination, $property->getValue($source));
        }
    }

    private function doTransform($object)
    {
        if ($object instanceof MongoId) {
            return (string) $object;
        }

        if ($object instanceof MongoDate) {
            return $this->toDateTime($object);
        }

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
            $property->setValue($object, $this->doTransform($value));
        }

        return $object;
    }

    private function transformArray(array $object)
    {
        foreach ($object as $key => $value) {
            $object[$key] = $this->doTransform($value);
        }

        if ($this->isHash($object)) {
            return $this->toArrayIterator($object);
        }

        return $object;
    }

    public function reverseTransform($object)
    {
        if (is_object($object)) {
            return $this->doReverseTranform(clone $object);
        }

        return $this->doReverseTranform($object);
    }

    private function doReverseTranform($object)
    {
        if (isset($object->_id) && !$object->_id instanceof MongoId) {
            $object->_id = $this->toMongoId($object->_id);
        }

        if ($object instanceof DateTime) {
            return $this->toMongoDate($object);
        }

        if (is_object($object)) {
            return $this->reverseTransformObject($object);
        }

        if (is_array($object)) {
            return $this->reverseTransformArray($object);
        }

        return $object;
    }

    private function reverseTransformObject($object)
    {
        $refl = new \ReflectionObject($object);
        foreach ($refl->getProperties() as $property) {

            $property->setAccessible(true);
            $value = $property->getValue($object);

            $property->setValue($object, $this->doReverseTranform($value));
        }

        return $object;
    }

    private function reverseTransformArray(array $object)
    {
        foreach ($object as $key => $value) {
            $object[$key] = $this->doReverseTranform($value);
        }

        return $object;
    }

    private function isHash(array $array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    private function toMongoId($id)
    {
        return new MongoId($id);
    }

    private function toMongoDate(DateTime $date)
    {
        return new MongoDate($date->format('U.u'));
    }

    private function toDateTime(MongoDate $date)
    {
        $dateTime = new DateTime;
        $dateTime->setTimestamp($date->sec);

        return $dateTime;
    }

    private function toArrayIterator(array $object)
    {
        return new ArrayIterator($object);
    }

    private function newInstance()
    {
        $factory = $this->factory;

        return $factory();
    }
}

