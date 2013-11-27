<?php

namespace spec\Knp\Raw\Mongo;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PhpSpec\Wrapper\Subject;

class RepositorySpec extends ObjectBehavior
{
    public function let(\MongoCollection $collection)
    {
        $this->beConstructedWith($collection, function() {
            return new \stdClass;
        });
    }

    public function it_finds_by_id($collection)
    {
        $collection->findOne([
            '_id' => new \MongoId('47cc67093475061e3d95369d')
        ])->willReturn([
            '_id' => new \MongoId('47cc67093475061e3d95369d'),
            'name' => 'test'
        ]);

        $object = new \stdClass;
        $object->_id = '47cc67093475061e3d95369d';
        $object->name = 'test';

        $this->find('47cc67093475061e3d95369d')->shouldBeLike($object);
    }

    public function it_transforms_raw_document_into_object()
    {
        $this->transform([])->shouldBeLike(new \stdClass);
    }
}
