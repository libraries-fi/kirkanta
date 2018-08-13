<?php

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

abstract class SearchFormType extends FormType
{
    public function getBlockPrefix() : string
    {
        return 'search_form';
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        $builder->setMethod('get');
        $this->form($builder, $options);

        foreach ($builder as $field) {
            $field->setRequired(false);
        }
    }
}
