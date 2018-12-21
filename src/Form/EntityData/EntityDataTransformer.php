<?php

namespace App\Form\EntityData;

use App\Util\FormData;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EntityDataTransformer implements DataTransformerInterface
{
    private $data_class;
    private $langcode;
    private $owner;

    public function __construct(string $data_class, string $langcode)
    {
        $this->data_class = $data_class;
        $this->langcode = $langcode;
    }

    public function transform($data)
    {
        return $data;
    }

    public function reverseTransform($norm_data)
    {
        $norm_data->setLangcode($this->langcode);
        // $norm_data->setEntity($this->owner);
        return $norm_data;
    }

    public function setOwnerEntity($owner) : void
    {

        $this->owner = $owner;
    }
}
