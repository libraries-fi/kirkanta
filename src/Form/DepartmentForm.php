<?php

namespace App\Form;

use App\Form\Type\StateChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class DepartmentForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\DepartmentDataType::class
            ]);

    }
}
