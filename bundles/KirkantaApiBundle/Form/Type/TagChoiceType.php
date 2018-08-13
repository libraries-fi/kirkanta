<?php

namespace KirjastotFi\KirkantaApiBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagChoiceType extends ChoiceType
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
            'multiple' => true,
        ]);
    }

    public function onPreSubmit(FormEvent $event) : void
    {
        if (!is_null($event->getData())) {
            $event->setData(explode(',', trim($event->getData())));
        }
    }
}
