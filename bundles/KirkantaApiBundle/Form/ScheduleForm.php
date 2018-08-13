<?php

namespace KirjastotFi\KirkantaApiBundle\Form;

use KirjastotFi\KirkantaApiBundle\Form\Type\FlexibleDateType;
use KirjastotFi\KirkantaApiBundle\Form\Type\MetaDateRange;
use KirjastotFi\KirkantaApiBundle\Form\Type\NestedDataType;
use KirjastotFi\KirkantaApiBundle\Form\Type\OrderByType;
use KirjastotFi\KirkantaApiBundle\Form\Type\TagChoiceType;
use KirjastotFi\KirkantaApiBundle\Form\Type\TagCollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class ScheduleForm extends ApiFormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('sort', OrderByType::class, [
                'empty_data' => ['organisation', 'department', 'opens'],
                'order_by' => [
                    'Organisation ID' => 'organisation',
                    'Department ID' => 'department',
                    'Opening Time' => 'opens',
                ]
            ])
            ->add('start', FlexibleDateType::class)
            ->add('end', FlexibleDateType::class, [
                'range_position' => 'end',
            ])
            ->add('organisation', NestedDataType::class)
            ->add('refs', TagChoiceType::class, [
                'choices' => [
                    'Periods' => 'period'
                ]
            ])
            ;
    }
}
