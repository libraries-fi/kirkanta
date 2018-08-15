<?php

namespace App\Form\Type;

use App\Entity\Address;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailAddressType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('street') 
            ->add('box_number')
            ->add('zipcode')
            ->add('area')
            ;
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => Address::class
        ]);
    }
}
