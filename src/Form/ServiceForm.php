<?php

namespace App\Form;

use App\Util\ServiceTypes;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceForm extends EntityFormType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        
        $options->setDefaults([
            'data_class' => \App\Entity\Service::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        $builder
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\ServiceDataType::class
            ])
            ->add('type', ChoiceType::class, [
                'placeholder' => '-- Select --',
                'choices' => new ServiceTypes()
            ])
            ;
    }
}
