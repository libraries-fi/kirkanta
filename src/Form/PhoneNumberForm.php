<?php

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TelType;

use App\Form\DataTransformer\PhoneNumberTransformer;

class PhoneNumberForm extends ContactInfoForm
{
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
