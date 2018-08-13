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
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            // ->add('info', null, [
            //     'required' => false
            // ])
            ->add('info', CollectionType::class, [
                'required' => false,
                'prototype' => TextType::class,
                // 'data' =>
            ])
            ->add('times', CollectionType::class, [
                'entry_type' => PeriodDayTimeType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
            ]);

        $builder->get('info')->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data) {
                foreach ($data as $langcode => $_) {
                    $form->add($langcode, null, [
                        'langcode' => $langcode,
                        'label' => false,
                    ]);
                }
            } else {
                $event->setData(['fi' => null]);

                $parent = $form->getParent();

                $form->add('fi', null, [
                    'langcode' => 'fi',
                    'label' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            // 'data_class' => ArrayObject::class
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options) : void
    {


        // exit;
    }
}
