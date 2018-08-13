<?php

namespace App\Doctrine;

use Doctrine\ORM\EntityRepository as BaseRepository;
use App\Entity\EntityBase;

use Symfony\Component\PropertyAccess\PropertyAccess;

class EntityRepository extends BaseRepository
{
    public function create(array $values = []) : EntityBase
    {
        /*
         * Set properties using the generic Symfony way instead of relying on Doctrine class metadata
         * since not all properties (e,g, $file) are mapped Doctrine fields.
         */
        $accessor = PropertyAccess::createPropertyAccessor();
        $class = $this->getEntityName();
        $entity = new $class;

        foreach ($values as $key => $value) {
            if (!is_null($value)) {
                $accessor->setValue($entity, $key, $value);
            }
        }

        return $entity;
    }
}
