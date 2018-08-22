<?php

namespace App\Form;

use App\Entity\Consortium;
use App\Util\ServicePointTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class ServicePointForm extends LibraryForm
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        /*
         * Replace unused fields with hidden values.
         * This allows us to re-use library form templates.
         */
        $builder
            ->add('parent', HiddenType::class, [
                'mapped' => false,
                'required' => false,
                'data' => null,
            ]);

        /*
         * Override fields to change options.
         */
        $builder
            ->add('type', ChoiceType::class, [
                'placeholder' => '-- Select --',
                'choices' => new ServicePointTypes
            ])
            ->add('consortium', EntityType::class, [
                'label' => 'Finna organisation',
                'required' => false,
                'class' => Consortium::class,
                'placeholder' => '-- Select --',
                'choice_label' => 'name',
                'query_builder' => function($repository) {
                    // NOTE: $this->queryBuilder is required by EntityTypeExtension.
                    $this->queryBuilder = $repository->createQueryBuilder('e')
                        ->innerJoin('e.finna_data', 'fd')
                        ->andWhere('fd.exclusive = true');
                    return $this->queryBuilder;
                }
            ]);
    }
}
