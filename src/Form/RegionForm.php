<?php

namespace App\Form;

use App\Entity\RegionData;
use Symfony\Component\Form\FormBuilderInterface;

class RegionForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\RegionDataType::class
            ])

            ;
    }
}
