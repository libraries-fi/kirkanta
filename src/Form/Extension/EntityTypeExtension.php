<?php

namespace App\Form\Extension;

use App\Entity\Feature\Translatable;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityTypeExtension extends AbstractTypeExtension
{
    private $queryBuilder;

    public function getExtendedType() : string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        $options->setDefault('query_builder', function($repository) {
            $this->queryBuilder = $repository->createQueryBuilder('e');
            return $this->queryBuilder;
        });

        $options->setDefault('translation_domain', false);
        $options->setDefault('choice_translation_domain', false);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        if (isset($this->queryBuilder) && is_string($options['choice_label'])) {
            if (is_a($options['class'], Translatable::class, true)) {
                $this->queryBuilder->addSelect('d')->join('e.translations', 'd');
                $prefix = 'd.';
            } else {
                $prefix = 'e.';
            }

            $this->queryBuilder->orderBy($prefix . $options['choice_label']);
        }
    }
}
