<?php

namespace App\Form;

use App\Form\Type\StateChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Base class for organisations and departments.
 */
abstract class FacilityForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('state', StateChoiceType::class);

    }
}
