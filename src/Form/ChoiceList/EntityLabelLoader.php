<?php

namespace App\Form\ChoiceList;

use RuntimeException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Feature\Translatable;
use App\I18n\Translations;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

use Doctrine\ORM\Query;

class EntityLabelLoader implements ChoiceLoaderInterface
{
    private $em;
    private $class;
    private $field;

    public function __construct(EntityManagerInterface $entity_manager, string $class, $field)
    {
        $this->em = $entity_manager;
        $this->class = $class;
        $this->field = $field;
    }

    public function loadChoiceList($value = null) : ChoiceListInterface
    {
        if (!is_null($value)) {
            throw new RuntimeException('Unsupported argument');
        }

        $builder = $this->em->createQueryBuilder()
            ->select('e.id')
            ->from($this->class, 'e');

        if (is_a($this->class, Translatable::class, true)) {
            $builder->join('e.data', 't', 'WITH', 't.langcode = :langcode');
            $builder->setParameter('langcode', Translations::DEFAULT_LANGCODE);
            $builder->addSelect('t.' . $this->field)->orderBy('t.' . $this->field);
        } else {
            $builder->addSelect('e.' . $this->field)->orderBy('e.' . $this->field);
        }

        $result = $builder->getQuery()->execute();
        $choices = [];

        $generator = function (array $result) {
            foreach ($result as $row) {
                yield (object)$row;
            }
        };

        return new ArrayChoiceList($generator($result), function ($object) {
            return $object->id;
        });

        exit('sdsd');
    }

    public function loadChoicesForValues(array $values, $value = null) : array
    {
        if (!is_null($value)) {
            throw new RuntimeException('Unsupported argument');
        }
    }

    public function loadValuesForChoices(array $choices, $value = null) : array
    {
        if (!is_null($value)) {
            var_dump($value);
            throw new RuntimeException('Unsupported argument');
        }

        $values = [];

        foreach ($choices as $i => $entity) {
            $values[$i] = $entity->getId();
        }

        return $values;
    }
}
