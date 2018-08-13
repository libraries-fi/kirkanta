<?php

namespace App\Form;

use App\Entity\Library;
use App\Entity\UserGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ConsortiumSearchForm extends SearchFormType
{
    protected function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('name')
            ->add('group', EntityType::class, [
                'placeholder' => '-- Any --',
                'class' => UserGroup::class,
            ])
            ->add('special', ChoiceType::class, [
                'label' => 'Type',
                'placeholder' => '-- Any --',
                'choices' => [
                    'Consortiums' => 0,
                    'Special Finna consortiums' => 1,
                ]
            ])
            ;
    }
}
