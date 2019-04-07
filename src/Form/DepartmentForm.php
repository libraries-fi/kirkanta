<?php

namespace App\Form;

use App\Form\Type\StateChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartmentForm extends EntityFormType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => \App\Entity\Department::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => new \App\Util\DepartmentTypes,
                'placeholder' => '-- Select --',
            ])
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\DepartmentDataType::class
            ]);
    }
}
