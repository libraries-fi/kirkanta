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
                'label' => 'Post office',
                'required' => false,
            ])
            ->add('info', null, [
                'label' => 'Directions',
                'help' => 'Short description for locating the building or entrance.',
                'required' => false,
            ])
            ;

        if ($options['address_type'] == 'mail_address') {
            $builder->get('area')->addModelTransformer(new CallbackTransformer('mb_strtoupper', 'mb_strtoupper'));
        }
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'address_type' => 'location',
        ]);
    }
}
