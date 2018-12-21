<?php

namespace App\Form;

use App\Form\DataTransformer\PhoneNumberTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneNumberForm extends ContactInfoForm
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => \App\Entity\PhoneNumber::class,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        $builder->add('contact', TelType::class, [
            'label' => 'Phone number'
        ]);

        $builder->get('contact')
          ->addModelTransformer(new PhoneNumberTransformer);
    }
}
