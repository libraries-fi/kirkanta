<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\ServiceInstance;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceInstanceForm extends EntityFormType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);

        $options->setDefaults([
            'data_class' => ServiceInstance::class
        ]);
    }
    
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        $builder
            ->add('template', EntityType::class, [
                'class' => Service::class,
                'placeholder' => '-- Select --',
                'choice_label' => 'name',
                'group_by' => 'type',
            ])
            ->add('for_loan', CheckboxType::class, [
                'required' => false,
                'label' => 'Available for loan',
            ])
            ->add('email', EmailType::class, [
                'required' => false,
            ])
            ->add('phone_number', TelType::class, [
                'required' => false
            ])
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\ServiceInstanceDataType::class
            ])
            ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) use($builder) {
            // Template field should be enabled only when creating new service instances.
            if ($event->getForm()->get('template')->getData()) {
                $event->getForm()->remove('template');
            }
        });
    }
}
