<?php

namespace App\Form;

use App\Entity\UserGroup;
use App\Security\Authorization\SystemRoles;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('username')
            ->add('email', EmailType::class)
            ->add('group', EntityType::class, [
                'placeholder' => '-- Any --',
                'class' => UserGroup::class,
                'choice_label' => 'name'
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Permissions',
                'placeholder' => '-- Select --',
                'expanded' => true,
                'multiple' => true,
                'choices' => (new SystemRoles)->getUserRoles(),
                'choice_attr' => function($key, $label) {
                    if ($key == 'ROLE_USER') {
                    // return ['disabled' => true];
                    }
                    return [];
                }
            ])

            ;
    }
}
