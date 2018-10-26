<?php

namespace App\Form\EntityData;

use App\Entity\PeriodData;
use App\Form\I18n\EntityDataType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeriodDataType extends EntityDataType
{
    protected $dataClass = PeriodData::class;

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', null, [
                'label' => 'Name',
                'langcode' => $options['langcode'],
            ])->add('description', TextareaType::class, [
                'label' => 'Description',
                'help' => 'Will be displayed below the schedules',
                'langcode' => $options['langcode'],
                'required' => false,
                'attr' => [
                    'rows' => 4,
                ]
            ]);
    }
}
