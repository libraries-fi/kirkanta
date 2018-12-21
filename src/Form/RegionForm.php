<?php

namespace App\Form;

use App\Entity\RegionData;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegionForm extends FormType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => \App\Entity\Region::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\RegionDataType::class
            ])

            ;
    }
}
