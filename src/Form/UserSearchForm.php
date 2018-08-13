<?php

namespace App\Form;

use App\Entity\Library;
use App\Entity\UserGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class UserSearchForm extends SearchFormType
{
    protected function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('name', null, [
                'label' => 'Name or email'
            ])
            ->add('group', EntityType::class, [
                'placeholder' => '-- Any --',
                'class' => UserGroup::class,
            ])
            ->add('role', ChoiceType::class, [
                'placeholder' => '-- Any --',
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Administrator' => 'ROLE_ADMIN',
                ]
            ]);
    }
}
