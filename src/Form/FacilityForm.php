<?php

namespace App\Form;

use App\Form\Type\StateChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Base class for organisations and departments.
 */
abstract class FacilityForm extends EntityFormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        $builder
            ->add('state', StateChoiceType::class);

    }
}
