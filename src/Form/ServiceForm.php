<?php

namespace App\Form;

use App\Util\ServiceTypes;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ServiceForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\ServiceDataType::class
            ])
            ->add('type', ChoiceType::class, [
                'placeholder' => '-- Select --',
                'choices' => new ServiceTypes
            ])
            ;
    }
}
