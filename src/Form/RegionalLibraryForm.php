<?php

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegionalLibraryForm extends FormType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => \App\Entity\RegionalLibrary::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\RegionalLibraryDataType::class
            ])

            ;
    }
}
