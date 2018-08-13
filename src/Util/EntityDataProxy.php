<?php

namespace App\Util;

use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\PropertyAccess\PropertyAccess;

use App\Entity\EntityBase;
use App\Entity\EntityDataBase;

class EntityDataProxy {
    public static function create($entity) : self
    {
        return new EntityDataProxy($entity);
    }

    public function __construct($entity)
    {
        $this->entity = $entity;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __get($key)
    {
        try {
            $value = $this->accessor->getValue($this->entity, $key);

            if ($value instanceof EntityBase || $value instanceof EntityDataBase) {
                return EntityDataProxy::create($value);
            } else {
                return $value;
            }
        } catch (FatalThrowableError $error) {
            exit('here');
            return '';
        }

        if ($this->entity->isNew()) {
            return '';
        } else {
            return $this->accessor->getValue($this->entity, $key);
        }
    }
}
