<?php

namespace App\Doctrine;

use App\Entity\Feature\Weight;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository as BaseRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EntityRepository extends BaseRepository
{
    private $propertyAccessor;

    public function create(array $values = [])
    {
        /*
         * Set properties using the generic Symfony way instead of relying on Doctrine class metadata
         * since not all properties (e,g, $file) are mapped Doctrine fields.
         */
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $class = $this->getEntityName();
        $entity = new $class;
        $definitions = $this->getClassMetadata();

        foreach ($values as $field => $value) {
            if ($field != 'translations') {
                if ($field_definition = $definitions->associationMappings[$field] ?? null) {
                    if (in_array($field_definition['type'], [ClassMetadata::ONE_TO_MANY, ClassMetadata::MANY_TO_MANY])) {
                        $this->pushIntoCollection($entity, $field, $value);
                        continue;
                    } elseif (is_array($value)) {
                        $value = $this->getEntityManager()->getRepository($field_definition['targetEntity'])->create($value);
                    }
                }
            }

            if (!is_null($value)) {
                $this->propertyAccessor->setValue($entity, $field, $value);
            }
        }

        return $entity;
    }

    /**
     * Updates weights of the items in the collection.
     *
     * NOTE: The collection itself will not be re-ordered because it isn't even possible because
     * it cannot be done in-place.
     */
    public function updateWeights(Collection $collection, array $weights = []) : void
    {
        if (!is_a($this->getClassName(), Weight::class, true)) {
            throw new \BadMethodCallException('Method is available only with entities that implement Weight');
        }

        $matched = $collection->matching(Criteria::create()->where(Criteria::expr()->in('id', array_keys($weights))));

        if (count($matched) > 0) {
            $start_index = $matched->first()->getWeight();

            foreach ($matched as $entity) {
                $entity->setWeight($weights[$entity->getId()]);
            }
        }

        /**
         * Continue re-weighting items. This makes sure that weights are consecutive values.
         *
         * Required e.g. when one of the entities has NULL ID because it won't be matched then as
         * array keys (in $weights) cannot be NULL.
         *
         * Also required when one of the entities as deleted.
         */

        $data = $collection->toArray();

        usort($data, function($a, $b) use ($weights) {
            $pa = $a->getWeight() ?? 9999;
            $pb = $b->getWeight() ?? 9999;
            $delta = $pa - $pb;

            if ($delta == 0) {
                // Move the entity whose weight is being updated up in the order.
                return isset($weights[$b->getId()]) - isset($weights[$a->getId()]);
            } else {
                return $delta;
            }
        });

        foreach (array_values($data) as $i => $entity) {
            $entity->setWeight($i);
        }
    }

    private function pushIntoCollection($entity, string $field, iterable $items) : void
    {
        $field_definition = $this->getClassMetadata()->associationMappings[$field];
        $collection = $this->propertyAccessor->getValue($entity, $field);

        foreach ($items as $item) {
            if (is_array($item)) {
                $item = $this->getEntityManager()->getRepository($field_definition['targetEntity'])->create($item);
                $collection->add($item);
            }
        }
    }
}
