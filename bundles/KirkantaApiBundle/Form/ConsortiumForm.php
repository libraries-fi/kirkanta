<?php

namespace KirjastotFi\KirkantaApiBundle\Form;

use KirjastotFi\KirkantaApiBundle\Form\Type\MetaDateRange;
use KirjastotFi\KirkantaApiBundle\Form\Type\NestedDataType;
use KirjastotFi\KirkantaApiBundle\Form\Type\OrderByType;
use KirjastotFi\KirkantaApiBundle\Form\Type\TagChoiceType;
use KirjastotFi\KirkantaApiBundle\Form\Type\TagCollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class ConsortiumForm extends ApiFormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('sort', OrderByType::class, [
                'empty_data' => ['name'],
                'order_by' => [
                    'Distance' => 'distance',
                    'Name' => 'name',
                    'City' => 'city',
                    'Consortium' => 'consortium',
                ]
            ])
            ->add('with', TagChoiceType::class, [
                'choices' => [
                    'Finna data' => 'finna',
                    'Link groups' => 'links',
                ]
            ])
            ->add('created', MetaDateRange::class)
            ->add('finna:id', null, [
                'required' => true
            ])
            ->add('finna:special', ChoiceType::class, [
                'empty_data' => '0',
                'choices' => [
                    'Regular organisations' => false,
                    'Special Finna organisations' => true,
                    'Any type' => 'any',
                ]
            ])
            ->add('id', TagCollectionType::class, [
                'entry_type' => IntegerType::class,
            ])
            ->add('modified', MetaDateRange::class)
            ->add('name')
            ->add('slug')


            ;
    }
}
