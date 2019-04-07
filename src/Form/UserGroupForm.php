<?php

namespace App\Form;

use App\Entity\UserGroup;
use App\Security\Authorization\SystemRoles;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class UserGroupForm extends EntityFormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        parent::form($builder, $options);

        $builder
            ->add('name')
            ->add('parent', EntityType::class, [
                'required' => false,
                'placeholder' => '-- Any --',
                'class' => UserGroup::class,
                'choice_label' => 'name'
            ])
            ->add('description', null, [
                'required' => false
            ])
            ->add('roles', ChoiceType::class, [
                'required' => false,
                'label' => 'Permissions',
                'placeholder' => '-- Select --',
                'expanded' => true,
                'multiple' => true,
                'choices' => (new SystemRoles)->getGroupRoles(),
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
