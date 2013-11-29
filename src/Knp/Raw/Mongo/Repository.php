<?php

namespace Knp\Raw\Mongo;

use \MongoCollection;
use \MongoCursor;
use \ArrayIterator;

class Repository
{
    private $collection;
    private $factory;

    public function __construct(MongoCollection $collection, callable $factory = null, Transformer $transformer = null)
    {
        $this->collection = $collection;
        $this->factory = $factory ?: function() { return new \stdClass; };
        $this->transformer = $transformer ?: new Transformer;
    }

    public function transform($object)
    {
        $destination = $this->newInstance();
        $this->copy((object) $object, $destination);

        return $this->transformer->toObject($destination);
    }

    public function find($id)
    {
        $object = $this->collection->findOne(['_id' => new \MongoId($id)]);
        $object = $this->transform($object);

        return $object;
    }

    public function findAll()
    {
        return $this->hydrateCursor($this->collection->find());
    }

    public function save($object)
    {
        $array = $this->transformer->toArray($object);

        return $this->collection->save($array);
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

