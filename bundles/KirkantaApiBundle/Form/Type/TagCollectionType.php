<?php

namespace KirjastotFi\KirkantaApiBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagCollectionType extends CollectionType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        parent::buildForm($builder, $options);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'], 1000);
    }

    public function configureOptions(OptionsResolver $options) : void
    {
        parent::configureOptions($options);
        $options->setDefaults([
            'allow_add' => true,
            'entry_type' => TextType::class
        ]);
    }

    public function onPreSubmit(FormEvent $event) : void
    {
        if (!is_null($event->getData())) {
            $event->setData(explode(',', trim($event->getData())));
        }
    }
}
