<?php

namespace App\Module\UserManagement\Form;

use UserAccountsBundle\UserInterface;
use App\Entity\UserGroup;
use App\Form\FormType;
use App\Security\Authorization\SystemRoles;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('username')
            ->add('email', EmailType::class)
            ->add('group', EntityType::class, [
                'label' => 'User group',
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
            ])
            ->add('expires', DateType::class, [
                'label' => 'Expiration date',
                'required' => false,
                'attr' => [
                    'data-no-custom'
                ],
                'placeholder' => [
                    'day' => '-- Day --',
                    'month' => '-- Month --',
                    'year' => '-- Year --'
                ]
            ])

            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use($options) {
            $user = $event->getData();

            if ($user instanceof UserInterface && !$user->isEnabled()) {
                $event->getForm()->remove('expires');
            }
        });
    }
}
