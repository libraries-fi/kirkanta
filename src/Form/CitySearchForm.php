<?php

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;

class CitySearchForm extends SearchFormType
{
    protected function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('name');
    }
}
