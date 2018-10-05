<?php

namespace App\Form\Type;

use App\Entity\Address;
use App\Form\EntityData\AddressDataType;
use App\Form\I18n\EntityDataCollectionType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailAddressType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('box_number')
            ->add('zipcode')
            ;
        $builder
            ->add('zipcode')
            ->add('coordinates', null, [
                'required' => false
            ])
            ->add('translations', EntityDataCollectionType::class, [
                'entry_type' => AddressDataType::class
            ])
            ;

        // $builder->get('area')->addModelTransformer(new CallbackTransformer('mb_strtoupper', 'mb_strtoupper'));
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => Address::class,
            'address_type' => 'location',
            'current_langcode' => null,
        ]);
    }
}
