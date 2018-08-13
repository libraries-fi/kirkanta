<?php

namespace KirjastotFi\KirkantaApiBundle\Form\Type;

use KirjastotFi\KirkantaApiBundle\Form\Type\TagCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NestedDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);

        foreach ($options['fields'] as $name => $entry_type) {
            if (is_array($entry_type)) {
                $entry_options = $entry_type;
                $entry_type = $entry_options['type'];
                unset($entry_options['type']);
            } else {
                $entry_options = [];
            }

            if (isset($entry_options['use_tags']) && $entry_options['use_tags'] == false) {
                unset($entry_options['use_tags']);
                $builder->add($name, $entry_type, $entry_options);
            } else {
                unset($entry_options['use_tags']);
                $builder->add($name, TagCollectionType::class, [
                    'entry_type' => $entry_type,
                    'entry_options' => $entry_options,
                ]);
            }

        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'], 1000);
        $builder->addModelTransformer(new CallbackTransformer(
            function($data) {
                return $data;
            },
            function($data) {
                $data = array_filter($data);

                if (empty($data)) {
                    return null;
                } else {
                    return $data;
                }
            }
        ));
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'fields' => [
                'id' => IntegerType::class,
                'slug' => TextType::class,
                'name' => [
                  'type' => TextType::class,
                  'use_tags' => false,
                ]

            ]
        ]);
    }

    public function onPreSubmit(FormEvent $event) : void
    {
        $value = $event->getData();

        if (!is_null($value) && !is_array($value)) {
            $event->setData(['id' => $value]);
        }
    }
}
