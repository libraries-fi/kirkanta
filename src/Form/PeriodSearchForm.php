<?php

namespace App\Form;

use App\Entity\Department;
use App\Entity\Library;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class PeriodSearchForm extends SearchFormType
{
    protected function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('name')
            ;

        if ($options['parent'] && $options['parent'] instanceof Library) {
            $builder->add('department', EntityType::class, [
                'class' => Department::class,
                'choices' => $options['parent']->getDepartments(),
                'placeholder' => '-- Any --',
            ]);
        }

        $builder->add('past_periods', CheckboxType::class, [
            'label' => 'Include past periods'
        ]);
    }
}
