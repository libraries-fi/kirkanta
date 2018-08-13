<?php

namespace App\Module\Finna\Form;

use App\Module\Finna\Entity\FinnaAdditions;
use App\Form\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtendConsortiumForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {

    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => FinnaAdditions::class,
        ]);
    }
}
