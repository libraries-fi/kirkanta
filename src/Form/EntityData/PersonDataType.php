<?php

namespace App\Form\EntityData;

use App\Entity\PersonData;
use App\Form\I18n\EntityDataType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonDataType extends EntityDataType
{
    protected $dataClass = PersonData::class;

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('job_title', null, [
                'label' => 'Job title',
                'langcode' => $options['langcode'],
                // 'required' => false,
            ])->add('responsibility', null, [
                'label' => 'Responsibility',
                'langcode' => $options['langcode'],
                'required' => false,
            ])
            ;
    }
}
