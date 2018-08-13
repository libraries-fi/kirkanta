<?php

namespace App\Form;

use App\Entity\Library;
use App\Form\Type\SimpleEntityType;
use App\Form\Type\StateChoiceType;
use App\Util\PersonQualities;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class PersonForm extends FormType
{
    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('state', StateChoiceType::class)
            // ->add('library', EntityType::class, [
            //     'class' => Library::class,
            //     'choice_label' => 'name',
            //     'placeholder' => '-- Select --',
            // ])
            ->add('library', ChoiceType::class, [
                'class' => Library::class,
                'choice_label' => 'name',
                'placeholder' => '-- Select --',
            ])
            ->add('first_name')
            ->add('last_name')
            ->add('email', EmailType::class, [
                // 'required' => false
            ])
            ->add('email_public', CheckboxType::class, [
                'required' => false
            ])
            ->add('phone', null, [
                'required' => false
            ])
            ->add('head', CheckboxType::class, [
                'required' => false,
                'label' => 'Head of organisation',
            ])
            ->add('qualities', ChoiceType::class, [
                'choices' => new PersonQualities,
                'multiple' => true,
                'required' => false,
                'expanded' => true,
            ])
            ->add('translations', I18n\EntityDataCollectionType::class, [
                'entry_type' => EntityData\PersonDataType::class
            ])

            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            if (!$event->getData()) {
                $event->setData(['email_public' => true]);
            }
        });
    }
}
