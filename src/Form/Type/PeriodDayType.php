<?php

namespace App\Form\Type;

use ArrayObject;
use App\Entity\Address;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Form\Type\TranslationsType;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class PeriodDayType extends BaseType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);

        $options->setDefaults([
            'available_languages' => []
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('info', CollectionType::class, [
                'required' => false,
                'help' => 'Name of the holiday etc.',
                'prototype' => TextType::class,
            ])
            ->add('times', CollectionType::class, [
                'entry_type' => PeriodDayTimeType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype_data' => [
                    'staff' => true
                ]
            ]);

        $langcodes = $options['available_languages'];

        /**
         * NOTE: Adding fields has to be done in an event listener in order
         * to add the fields also on the prototype element.
         */
        $builder->get('info')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($langcodes) {
            $form = $event->getForm();

            /**
             * FIXME: Sometimes forms seem pre-populated with multiple fields
             * but they don't have the 'langcode' property set and don't match
             * available languages...
             */
            foreach ($form as $name => $_) {
                $form->remove($name);
            }

            foreach ($langcodes as $langcode) {
                $form->add($langcode, null, [
                    'langcode' => $langcode,
                    'label' => false,
                ]);
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $day = $event->getData();

            usort($day['times'], function (array $a, array $b) {
                return strcasecmp($a['opens'], $b['opens']);
            });

            $event->setData($day);
        });
    }
}
