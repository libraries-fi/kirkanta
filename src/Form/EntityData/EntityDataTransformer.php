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
        if (is_array($norm_data)) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $translation = new $this->data_class($this->langcode);

            foreach ($norm_data as $key => $value) {
                $accessor->setValue($translation, $key, $value);
            }

            if ($this->owner) {
                $accessor->setValue($translation, 'entity', $this->owner);
            }

            return $translation;
        } else {
            return $norm_data;
        }
    }

    public function setOwnerEntity($owner) : void
    {
        $this->owner = $owner;
    }
}
