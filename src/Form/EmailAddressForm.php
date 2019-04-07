<?php

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailAddressForm extends ContactInfoForm
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        
        $options->setDefaults([
            'data_class' => \App\Entity\EmailAddress::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        $builder->add('contact', EmailType::class, [
            'label' => 'Email address'
        ]);
    }
}
