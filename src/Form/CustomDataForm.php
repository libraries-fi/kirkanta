<?php

namespace App\Form;

use App\Entity\Consortium;
use App\Entity\Region;
use App\Entity\RegionalLibrary;
use App\Form\I18n\ContentLanguageChoiceType;
use App\Util\PersonQualities;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomDataForm extends FormType
{
    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'data_class' => \stdClass::class,
            'available_languages' => []
        ]);
    }

    public function form(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('id', null, [
                'help' => 'Machine-readable identifier.',
            ])
            ->add('title', CollectionType::class, [
                'help' => 'Informative name for users.',
                'prototype' => TextType::class,
                'required' => false,
            ])
            ->add('value', CollectionType::class, [
                'prototype' => TextareaType::class,
                'required' => false,
            ])
            ->add('content_language', ContentLanguageChoiceType::class, [
                'mapped' => false,
                'enabled_languages' => $options['available_languages']
            ])
            ;

        $builder->get('content_language')->setData($options['current_langcode']);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $entry = $event->getData();
            $entry->title = get_object_vars($entry->title);
            $entry->value = get_object_vars($entry->value);
        });

        $builder->get('title')->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $form = $event->getForm();
            $config = $form->getRoot()->getConfig();
            $langcodes = $config->getOption('available_languages') ?: [];
            $langcodes[] = $config->getOption('current_langcode');

            foreach ($langcodes as $langcode) {
                $form->add($langcode, null, [
                    'langcode' => $langcode,
                    'label' => false
                ]);
            }
        });

        $builder->get('value')->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $form = $event->getForm();
            $config = $form->getRoot()->getConfig();
            $langcodes = $config->getOption('available_languages') ?: [];
            $langcodes[] = $config->getOption('current_langcode');

            foreach ($langcodes as $langcode) {
                $form->add($langcode, TextareaType::class, [
                    'langcode' => $langcode,
                    'label' => false,
                    'attr' => [
                        'rows' => 4
                    ]
                ]);
            }
        });
    }
}
