<?php

namespace App\Module\ServiceMergeTool\Form\Type;

use App\Entity\Service;
use App\Form\EntityData\ServiceDataType;
use App\Form\I18n\EntityDataCollectionType;
use App\Util\ServiceTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'placeholder' => '-- Select --',
                'choices' => new ServiceTypes()
            ])
            ->add('translations', EntityDataCollectionType::class, [
                'entry_type' => ServiceDataType::class
            ]);
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => Service::class,
            'current_langcode' => 'fi',
        ]);
    }
}
