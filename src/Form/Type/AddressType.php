<?php

namespace App\Form\Type;

use App\Entity\Address;
use App\Entity\City;
use App\Form\EntityData\AddressDataType;
use App\Form\I18n\EntityDataCollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('zipcode')
            ->add('city', EntityType::class, [
                'placeholder' => '-- Select --',
                'class' => City::class,
                'choice_label' => 'name',
            ])
            ->add('coordinates', null, [
                'required' => false
            ])
            ->add('translations', EntityDataCollectionType::class, [
                'entry_type' => AddressDataType::class,
            ])
            ;

        // $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
        //
        //     $entity = $event->getData();
        //
        //     // foreach ($entity->getTranslato)
        //
        //     // var_Dump($entity->getTranslations());
        //
        //
        //
        //
        //     // exit('ssdada');
        // });
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);

        $options->setDefaults([
            'data_class' => Address::class,
            'langcode' => null,
            'current_langcode' => null,
        ]);
    }
}
