<?php

namespace KirjastotFi\KirkantaApiBundle\Form;

use KirjastotFi\KirkantaApiBundle\Form\Type\MetaDateRange;
use KirjastotFi\KirkantaApiBundle\Form\Type\NestedDataType;
use KirjastotFi\KirkantaApiBundle\Form\Type\OrderByType;
use KirjastotFi\KirkantaApiBundle\Form\Type\TagCollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class PersonForm extends ApiFormType
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
            ->add('created', MetaDateRange::class)
            ->add('head', ChoiceType::class, [
                'choices' => [
                    'Is boss' => true,
                    'Is not boss' => false,
                ]
            ])
            ->add('id', TagCollectionType::class, [
                'entry_type' => IntegerType::class,
            ])
            ->add('modified', MetaDateRange::class)
            ->add('name')
            ->add('organisation', NestedDataType::class)
            ;
    }
}
