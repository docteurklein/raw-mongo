<?php

namespace spec\Knp\Raw\Transformer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ToObjectSpec extends ObjectBehavior
{
    function it_transforms_recursivly()
    {
        $array = [[['test']]];
        $object = $this->transform($array);
        die(var_dump($array, $object->getWrappedObject()));;
        $expected = new \stdClass;
        $expexted[0] = new \stdClass;
        $expexted[0][0] = new \stdClass;
        $this->transform([[[]]])->shouldBeLike($expected);
    }

    function it_transforms_list_in_object()
    {

    }

    function it_transforms_hash_in_array_iterator()
    {

    }
}
