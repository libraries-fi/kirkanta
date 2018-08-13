<?php

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;

class RegionalLibraryForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\RegionalLibraryDataType::class
            ])

            ;
    }
}
