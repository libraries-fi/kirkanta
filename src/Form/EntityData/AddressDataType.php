<?php

namespace App\Form\EntityData;

use App\Entity\AddressData;
use App\Form\I18n\EntityDataType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressDataType extends EntityDataType
{
    protected $dataClass = AddressData::class;

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('street')
            ->add('area', null, [
                'required' => false,
            ])
            ->add('info', null, [
                'required' => false,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'address_type' => 'location',
        ]);
    }
}
