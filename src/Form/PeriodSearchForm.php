<?php

namespace App\Form;

use App\Util\PeriodSections;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class PeriodSearchForm extends SearchFormType
{
    protected function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('name')
            ->add('section', ChoiceType::class, [
                'choices' => new PeriodSections,
                'placeholder' => '-- Any --',
            ])
            ->add('only_valid', CheckboxType::class)

            ;
    }
}
