<?php

namespace App\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class SearchFormType extends FormType
{
    public function getBlockPrefix() : string
    {
        return 'search_form';
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'parent' => null,
            'data_class' => \App\Util\FormData::class,
        ]);
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
