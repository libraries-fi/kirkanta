<?php

namespace App\Module\UserManagement\Form;

use App\Entity\User;
use App\Entity\UserGroup;
use App\Form\FormType;
use App\Security\Authorization\SystemRoles;
use App\Util\PasswordGenerator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class UserForm extends FormType
{
    /**
     * Quick switch to add or remove password field on the form.
     *
     * Could be toggled on in case there is a problem with the email system.
     */
    const FORCE_ENABLE_PASSWORD = true;

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => User::class,
            'enable_password' => false,
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $options['enable_password'] |= self::FORCE_ENABLE_PASSWORD;

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
                'choices' => (new SystemRoles())->getUserRoles(),
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

        if (!$this->auth->isGranted('ROLE_ROOT')) {
            $builder->remove('group');
        }

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
            $user = $event->getData();
            $form = $event->getForm();

            if ($options['enable_password']) {
                $form->add('password', RepeatedType::class, [
                    'required' => $user->isNew(),
                    'property_path' => 'raw_password',
                    'type' => $user->isNew() ? TextType::class : PasswordType::class,
                    'first_options' => [
                        'label' => 'New password',
                        'attr' => [
                            'minlength' => 8,
                            'maxlength' => 100,
                        ]
                    ],
                    'second_options' => [
                        'label' => 'Repeat password',
                        'attr' => [
                            'minlength' => 8,
                            'maxlength' => 100,
                        ]
                    ],
                    'constraints' => [
                        new Constraints\Length(['min' => 8, 'max' => 100])
                    ]
                ]);

                if ($user->isNew()) {
                    $form->get('password')->setData(PasswordGenerator::password());
                } elseif (!$this->auth->isGranted('ROLE_ROOT')) {
                    $form->remove('password');
                }
            }

            if (!$user->isEnabled()) {
                $form->remove('expires');
            }
        });
    }
}
