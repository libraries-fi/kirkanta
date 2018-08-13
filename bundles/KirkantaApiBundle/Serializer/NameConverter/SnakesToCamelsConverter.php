<?php

namespace KirjastotFi\KirkantaApiBundle\Serializer\NameConverter;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class SnakesToCamelsConverter extends CamelCaseToSnakeCaseNameConverter
{
    public function normalize($key) : string
    {
        return parent::denormalize($key);
    }

    public function denormalize($key) : string
    {
        return parent::normalize($key);
    }
}
