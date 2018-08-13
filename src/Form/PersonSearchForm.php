<?php

namespace App\Form;

use App\Entity\Library;
use App\Entity\UserGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;

class PersonSearchForm extends SearchFormType
{
    protected function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('name')
            // ->add('organisation', EntityType::class, [
            //     'class' => Library::class,
            //     'choice_label' => 'name',
            //     'placeholder' => '-- Any --',
            // ])
            ->add('group', EntityType::class, [
                'class' => UserGroup::class,
                'choice_label' => 'name',
                'placeholder' => '-- Any --',
            ])
            ;
    }
}
